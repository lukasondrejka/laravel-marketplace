/**
 * Format date
 *
 * @param date {string} Date
 * @returns {string} Formatted date
 */
export function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
