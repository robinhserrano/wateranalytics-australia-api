import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
) {
    return toUrl(urlToCheck) === currentUrl;
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

export function getRoleStyle(roleName: string) {
    const role = roleName.toLowerCase().trim();

    const colors: Record<string, { bg: string, text: string }> = {
        'admin': { bg: '#6D28D9', text: '#FFFFFF' },
        'sales manager': { bg: '#1E40AF', text: '#FFFFFF' },
        'sales team manager': { bg: '#0F766E', text: '#FFFFFF' },
        'sales person': { bg: '#F97316', text: '#FFFFFF' },
        'account officer': { bg: '#334155', text: '#FFFFFF' }
    };

    const style = colors[role] || { bg: '#94a3b8', text: '#FFFFFF' }; // Default to slate-400

    return {
        backgroundColor: style.bg,
        color: style.text,
        borderColor: style.bg
    };
}
