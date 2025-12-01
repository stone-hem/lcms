// utils/displayErrors.ts
import { toast } from 'vue-sonner'

/**
 * Display errors from various formats to the user using toast notifications
 * Handles 10+ different error formats from Laravel/Inertia
 */
export function displayErrors(errors: any): void {
  if (!errors) return

  const messages: string[] = []

  // Format 1: Simple object with field: message
  // { "end_datetime": "The end datetime field is required." }
  if (typeof errors === 'object' && !Array.isArray(errors)) {
    Object.entries(errors).forEach(([field, value]) => {
      if (typeof value === 'string') {
        messages.push(value)
      }
      // Format 2: Field with array of messages
      // { "email": ["The email is required.", "The email must be valid."] }
      else if (Array.isArray(value)) {
        value.forEach((msg) => {
          if (typeof msg === 'string') {
            messages.push(msg)
          } else if (typeof msg === 'object' && msg.message) {
            messages.push(msg.message)
          }
        })
      }
      // Format 3: Nested object errors
      // { "user": { "name": "Name is required" } }
      else if (typeof value === 'object' && value !== null) {
        Object.values(value).forEach((nested) => {
          if (typeof nested === 'string') {
            messages.push(nested)
          } else if (Array.isArray(nested)) {
            nested.forEach((msg) => {
              if (typeof msg === 'string') messages.push(msg)
            })
          }
        })
      }
    })
  }
  // Format 4: Array of error messages
  // ["Error 1", "Error 2"]
  else if (Array.isArray(errors)) {
    errors.forEach((error) => {
      if (typeof error === 'string') {
        messages.push(error)
      }
      // Format 5: Array of error objects
      // [{ message: "Error 1" }, { message: "Error 2" }]
      else if (typeof error === 'object' && error !== null) {
        if (error.message) {
          messages.push(error.message)
        } else if (error.error) {
          messages.push(error.error)
        } else if (error.msg) {
          messages.push(error.msg)
        } else {
          messages.push(JSON.stringify(error))
        }
      }
    })
  }
  // Format 6: Single string error
  // "Something went wrong"
  else if (typeof errors === 'string') {
    messages.push(errors)
  }
  // Format 7: Error object with message property
  // { message: "Something went wrong" }
  else if (errors.message) {
    messages.push(errors.message)
  }
  // Format 8: Error object with error property
  // { error: "Something went wrong" }
  else if (errors.error) {
    messages.push(errors.error)
  }
  // Format 9: Error object with errors array
  // { errors: ["Error 1", "Error 2"] }
  else if (errors.errors) {
    displayErrors(errors.errors) // Recursive call
    return
  }
  // Format 10: Validation errors format
  // { validation: { field: "message" } }
  else if (errors.validation) {
    displayErrors(errors.validation) // Recursive call
    return
  }

  // Display all collected messages
  if (messages.length === 0) {
    // Fallback for unknown formats
    toast.error('An error occurred. Please try again.')
    return
  }

  if (messages.length === 1) {
    toast.error(messages[0])
  } else {
    // Show multiple errors with slight delay between each
    messages.forEach((msg, index) => {
      setTimeout(() => {
        toast.error(msg)
      }, index * 150)
    })
  }
}

/**
 * Display success message
 */
export function displaySuccess(message: string): void {
  toast.success(message)
}

/**
 * Display warning message
 */
export function displayWarning(message: string): void {
  toast.warning(message)
}

/**
 * Display info message
 */
export function displayInfo(message: string): void {
  toast.info(message)
}

/**
 * Handle Inertia form errors directly
 * Usage: onError: (errors) => handleFormErrors(errors)
 */
export function handleFormErrors(errors: any): void {
  displayErrors(errors)
}