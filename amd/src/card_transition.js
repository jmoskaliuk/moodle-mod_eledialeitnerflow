// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * AMD module: card transition animation between Leitner boxes.
 *
 * Shows a brief animation on the current question page after answering,
 * highlighting which box the card moved to. After 1 second, redirects
 * to the next question.
 *
 * @module     mod_leitnerflow/card_transition
 * @package    mod_leitnerflow
 * @copyright  2024 eLeDia GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    'use strict';

    return {
        /**
         * Animate the box-flow pills, show feedback, then redirect.
         *
         * @param {number} fromBox - The box the card was in before.
         * @param {number} toBox - The box the card moved to.
         * @param {number} correct - 1 if the answer was correct, 0 if not.
         * @param {number} learned - 1 if the card is now learned, 0 if not.
         * @param {string} nextUrl - URL to redirect to after animation.
         */
        init: function(fromBox, toBox, correct, learned, nextUrl) {
            // Find the pill for the target box and briefly highlight it.
            var pills = document.querySelectorAll('[data-box]');

            pills.forEach(function(pill) {
                var boxNum = parseInt(pill.getAttribute('data-box'), 10);
                if (!boxNum) {
                    return;
                }
                if (boxNum === toBox && toBox !== fromBox) {
                    // Highlight target box with glow.
                    pill.classList.add('lf-anim-pulse-in');
                    if (correct) {
                        pill.style.boxShadow = '0 0 12px rgba(102, 153, 51, 0.6)';
                    } else {
                        pill.style.boxShadow = '0 0 12px rgba(249, 128, 18, 0.6)';
                    }
                }
            });

            // Redirect to next question after 1 second.
            if (nextUrl) {
                setTimeout(function() {
                    window.location.href = nextUrl;
                }, 1000);
            }
        }
    };
});
