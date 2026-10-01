/*
 * Closing a pop-up by clicking beside it — but only a click that started
 * there too. Selecting text in a field and letting go past the window's edge
 * makes the browser fire a click on the backdrop, which used to close the
 * window mid-edit.
 *
 *     const dayBackdrop = backdrop(() => (editingDay.value = null));  // once, in setup
 *     <div class="fixed inset-0 …" v-on="dayBackdrop">
 */
export function backdrop(close) {
    let pressed = null;

    return {
        mousedown: (event) => (pressed = event.target),
        click: (event) => {
            if (event.target === event.currentTarget && pressed === event.currentTarget) {
                close();
            }
            pressed = null;
        },
    };
}
