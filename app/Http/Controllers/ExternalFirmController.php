<?php

namespace App\Http\Controllers;

use App\Models\ExternalFirm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ExternalFirmController extends Controller
{
    public function index(Request $request)
        {
            $perPage = $request->ipp ?? 15;
            $search = $request->s ?? '';
    
            $firms = ExternalFirm::query()
                ->when($search, fn($q) => $q->where('firm_name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('kra_pin', 'ilike', "%{$search}%"))
                ->latest()
                ->paginate($perPage);
    
            return Inertia::render('users/External', [
                'firms' => $firms,
                'filters' => ['s' => $search]
            ]);
        }
    
        public function store(Request $request)
        {
            $request->validate([
                'firm_name'            => 'required|string|min:2',
                'bank_name'            => 'required|string',
                'bank_branch'          => 'nullable|string',
                'bank_account_number'  => 'required|string',
                'postal_address'       => 'required|string',
                'kra_pin'              => 'required|string|size:11', // e.g. A001234567B
                'email'                => 'nullable|email|unique:users,email|required_if:can_login,true',
                'password'             => 'required_if:can_login,true|min:8',
                'can_login'            => 'boolean',
            ]);
    
            try {
                DB::transaction(function () use ($request) {
                    $firm = ExternalFirm::create([
                        'firm_name'           => $request->firm_name,
                        'bank_name'           => $request->bank_name,
                        'bank_branch'         => $request->bank_branch,
                        'bank_account_number' => $request->bank_account_number,
                        'postal_address'      => $request->postal_address,
                        'kra_pin'             => $request->kra_pin,
                        'email'               => $request->can_login ? $request->email : null,
                        'can_login'           => $request->boolean('can_login'),
                    ]);
    
                    if ($request->boolean('can_login')) {
                        $user = User::create([
                            'name'               => $request->firm_name,
                            'email'              => $request->email,
                            'password'           => Hash::make($request->password),
                            'can_login'          => true,
                            'is_active'          => true,
                            'is_external_counsel'=> true,
                            'email_verified_at'  => now(), // or send verification
                            'profile_photo_path' => null,
                            'role_id'            => 3
                        ]);
    
                        // Link user → firm
                        $firm->user_id = $user->id;
                        $firm->save();
    
                        // Optional: Send welcome email
                        // WelcomeExternalCounsel::dispatch($user, $request->password);
                    }
                });
    
                return redirect()->back()->with('success', 'External counsel created successfully');
    
            } catch (\Exception $e) {
                \Log::error('External Firm Creation Failed: ' . $e->getMessage());
    
                return redirect()->back()->with('error', 'Failed to create external counsel: ' . $e->getMessage());
            }
        }
    
        public function update(Request $request, ExternalFirm $externalFirm)
        {
            $request->validate([
                'firm_name'            => 'required|string|min:2',
                'bank_name'            => 'required|string',
                'bank_branch'          => 'nullable|string',
                'bank_account_number'  => 'required|string',
                'postal_address'       => 'required|string',
                'kra_pin'              => 'required|string|size:11',
                'email'                => 'nullable|email|unique:users,email,' . ($externalFirm->user_id ?? 'NULL') . '|required_if:can_login,true',
                'password'             => 'nullable|string|min:8', // optional during update
                'can_login'            => 'required|boolean',
            ]);
        
            try {
                DB::transaction(function () use ($request, $externalFirm) {
                    $shouldHaveLogin = $request->boolean('can_login');
                    $currentUser = $externalFirm->user; // may be null
        
                    // 1. Update the ExternalFirm record
                    $externalFirm->update([
                        'firm_name'           => $request->firm_name,
                        'bank_name'           => $request->bank_name,
                        'bank_branch'         => $request->bank_branch,
                        'bank_account_number' => $request->bank_account_number,
                        'postal_address'      => $request->postal_address,
                        'kra_pin'             => $request->kra_pin,
                        'email'               => $shouldHaveLogin ? $request->email : null,
                        'can_login'           => $shouldHaveLogin,
                    ]);
        
                    // 2. Handle User Login Logic
                    if ($shouldHaveLogin) {
                        $email = $request->email;
        
                        // Case A: User already exists → update it
                        if ($currentUser) {
                            $updateData = [
                                'name'     => $request->firm_name,
                                'email'    => $email,
                                'can_login' => true,
                                'is_active' => true,
                                'is_external_counsel' => true,
                            ];
        
                            // Only update password if provided
                            if ($request->filled('password')) {
                                $updateData['password'] = Hash::make($request->password);
                            }
        
                            $currentUser->update($updateData);
        
                        } else {
                            // Case B: First time enabling login → create new user
                            if (!$request->filled('password')) {
                                throw new \Exception('Password is required when enabling login for the first time.');
                            }
        
                            $newUser = User::create([
                                'name'                => $request->firm_name,
                                'email'               => $email,
                                'password'            => Hash::make($request->password),
                                'can_login'           => true,
                                'is_active'           => true,
                                'is_external_counsel' => true,
                                'email_verified_at'   => now(),
                                'role_id'             => 3, // adjust as needed
                            ]);
        
                            // Link back to firm
                            $externalFirm->user_id = $newUser->id;
                            $externalFirm->save();
                        }
        
                    } else {
                        // Case C: Login is being disabled
                        $externalFirm->email = null; // clear stored email
                        $externalFirm->save();
        
                        if ($currentUser) {
                            $currentUser->update([
                                'can_login' => false,
                                'is_active' => false,
                            ]);
                            // Optionally: keep user for history, or soft-delete
                            // $currentUser->delete();
                        }
                    }
                });
        
                return redirect()->back()->with('success', 'External firm updated successfully');
        
            } catch (\Exception $e) {
                \Log::error('External Firm Update Failed: ' . $e->getMessage());
        
                return redirect()->back()->with('error', 'Failed to update external firm: ' . $e->getMessage());
            }
        }
    
        public function destroy(ExternalFirm $externalFirm)
        {
            $externalFirm->delete();
            return back()->with('success', 'External firm deleted.');
        }
}
