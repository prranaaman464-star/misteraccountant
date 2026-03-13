import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export type BreadcrumbItem = {
    title: string;
    href?: string;
};

export type NavSubItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    badge?: string;
    badgeIcon?: LucideIcon;
    items?: NavSubItem[];
    /** Custom function to determine if this item is active. Receives current pathname. */
    activeWhen?: (currentPath: string) => boolean;
};

export type NavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
    items?: NavSubItem[];
};
