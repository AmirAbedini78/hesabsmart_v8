/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.7.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */
import confetti from 'canvas-confetti'

function throwConfetti() {
  var confettiCanvas = document.createElement('canvas')

  confettiCanvas.classList.add(
    'absolute',
    'bottom-0',
    'w-full',
    'h-full',
    'confetti'
  )

  document.body.appendChild(confettiCanvas)

  var myConfetti = confetti.create(confettiCanvas, {
    resize: true,
  })

  myConfetti({
    particleCount: 300,
    spread: 300,
    ticks: 150,
    origin: {
      x: 1,
    },
  }).then(() => {
    const newCanvas = confettiCanvas.cloneNode(true)
    // Removed any event listeners
    confettiCanvas.replaceWith(newCanvas)
    confettiCanvas.remove()
    // Not sure if it's needed to remove?
    newCanvas.remove()
  })
}

export default throwConfetti
