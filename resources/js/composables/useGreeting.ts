import { computed } from 'vue';

/**
 * Returns time-based greeting: Good Morning, Good Afternoon, or Good Evening
 */
export function useGreeting(): { greeting: string } {
    const greeting = computed(() => {
        const hour = new Date().getHours();
        if (hour >= 5 && hour < 12) return 'Good Morning';
        if (hour >= 12 && hour < 17) return 'Good Afternoon';
        return 'Good Evening';
    });

    return { greeting };
}
