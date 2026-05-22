const display = document.querySelector('[data-run-display]');
const secondsInput = document.querySelector('[data-run-seconds]');
const startButton = document.querySelector('[data-run-start]');
const pauseButton = document.querySelector('[data-run-pause]');
const resetButton = document.querySelector('[data-run-reset]');

let elapsedSeconds = Number(secondsInput ? secondsInput.value : 0);
let timerId = null;

function formatTime(totalSeconds) {
    const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
    const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
    const seconds = String(totalSeconds % 60).padStart(2, '0');

    return `${hours}:${minutes}:${seconds}`;
}

function render() {
    if (display) {
        display.textContent = formatTime(elapsedSeconds);
    }

    if (secondsInput) {
        secondsInput.value = String(elapsedSeconds);
    }
}

function startTimer() {
    if (timerId) {
        return;
    }

    timerId = window.setInterval(() => {
        elapsedSeconds += 1;
        render();
    }, 1000);
}

function pauseTimer() {
    window.clearInterval(timerId);
    timerId = null;
}

function resetTimer() {
    pauseTimer();
    elapsedSeconds = 0;
    render();
}

if (startButton) {
    startButton.addEventListener('click', startTimer);
}

if (pauseButton) {
    pauseButton.addEventListener('click', pauseTimer);
}

if (resetButton) {
    resetButton.addEventListener('click', resetTimer);
}

render();
