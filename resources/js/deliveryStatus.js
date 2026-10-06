// Resend's words, as the office reads them — shared by every bulk-email screen.
const labels = {
    sent: 'sent, not yet delivered',
    delivered: 'delivered',
    delivery_delayed: 'delayed',
    bounced: 'bounced',
    complained: 'marked as spam',
    opened: 'opened',
    clicked: 'clicked',
    failed: 'failed to send',
    logged: 'logged (local, not sent)',
};

export const statusLabel = (status) => labels[status] || status;

export function statusClass(status) {
    if (['delivered', 'opened', 'clicked'].includes(status)) return 'bg-green-100 text-green-800';
    if (['bounced', 'complained', 'failed'].includes(status)) return 'bg-red-100 text-red-800';
    if (status === 'delivery_delayed') return 'bg-amber-100 text-amber-800';
    return 'bg-gray-100 text-gray-700';
}
