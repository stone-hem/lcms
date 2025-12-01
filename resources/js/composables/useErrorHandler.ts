import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { watch } from 'vue';

export function useErrorHandler() {
    const page = usePage();

    // Function to format error messages
    const formatError = (key: string, value: any): string[] => {
        const messages: string[] = [];

        if (Array.isArray(value)) {
            // Handle array of errors
            value.forEach(err => {
                if (typeof err === 'string') {
                    messages.push(err);
                } else if (typeof err === 'object') {
                    messages.push(JSON.stringify(err));
                }
            });
        } else if (typeof value === 'object' && value !== null) {
            // Handle nested objects
            Object.entries(value).forEach(([nestedKey, nestedValue]) => {
                if (Array.isArray(nestedValue)) {
                    nestedValue.forEach(msg => messages.push(msg));
                } else if (typeof nestedValue === 'string') {
                    messages.push(nestedValue);
                } else {
                    messages.push(JSON.stringify(nestedValue));
                }
            });
        } else if (typeof value === 'string') {
            messages.push(value);
        } else {
            messages.push(String(value));
        }

        return messages;
    };

    // Watch for errors in page props
    watch(
        () => page.props.errors,
        (errors) => {
            if (!errors || Object.keys(errors).length === 0) return;

            // Collect all error messages
            const allMessages: string[] = [];

            Object.entries(errors).forEach(([key, value]) => {
                const messages = formatError(key, value);
                allMessages.push(...messages);
            });

            // Display errors
            if (allMessages.length === 1) {
                toast.error(allMessages[0]);
            } else if (allMessages.length > 0) {
                // Show multiple errors
                allMessages.forEach((msg, index) => {
                    setTimeout(() => {
                        toast.error(msg);
                    }, index * 100); // Stagger toasts slightly
                });
            }
        },
        { deep: true, immediate: true }
    );

    // Watch for flash messages (success, info, warning)
    watch(
        () => page.props.flash,
        (flash: any) => {
            if (!flash) return;

            if (flash.success) {
                toast.success(flash.success);
            }
            if (flash.error) {
                toast.error(flash.error);
            }
            if (flash.warning) {
                toast.warning(flash.warning);
            }
            if (flash.info) {
                toast.info(flash.info);
            }
            if (flash.message) {
                toast(flash.message);
            }
        },
        { deep: true, immediate: true }
    );
}