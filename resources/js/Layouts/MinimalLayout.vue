<script>
document.addEventListener("mousemove", parallax);
let counter = 0;
const updateRate = 13;

function parallax(event) {
    this.querySelectorAll(".parallax-wcm .wcm-logo").forEach((shift) => {
        if (isTimeToUpdate()) {
            const rando = Math.random() * 5 | 0;
            const x = (event.pageX * rando) / 100;
            const y = (event.pageY * rando) / 100;

            shift.style.transform = `translateX(${x}px) translateY(${y}px)`;
        }
    });
}
function isTimeToUpdate() {
    return counter++ % updateRate === 0;
}
export default {
    mounted() {
        document.body.classList.add('red');
    }
}
</script>

<template>
    <main id="splash">
        <slot />
    </main>
</template>
