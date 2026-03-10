import { ref, onMounted, onUnmounted } from 'vue';

const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

function formatDateTime(): string {
    const now = new Date();
    const day = days[now.getDay()];
    const date = now.getDate();
    const month = months[now.getMonth()];
    const year = now.getFullYear();
    const hours = now.getHours();
    const minutes = now.getMinutes();
    const ampm = hours >= 12 ? 'PM' : 'AM';
    const hour12 = hours % 12 || 12;
    const minStr = minutes < 10 ? `0${minutes}` : `${minutes}`;
    return `${day}, ${date} ${month} ${year} • ${hour12}:${minStr} ${ampm}`;
}

/**
 * Returns formatted current date & time, updates every minute
 * Format: "Friday, 24 Mar 2025 • 11:34 AM"
 */
export function useDateTime(): { formatted: string } {
    const formatted = ref(formatDateTime());
    let intervalId: ReturnType<typeof setInterval>;

    onMounted(() => {
        intervalId = setInterval(() => {
            formatted.value = formatDateTime();
        }, 60000); // Update every minute
    });

    onUnmounted(() => {
        if (intervalId) clearInterval(intervalId);
    });

    return { formatted };
}
