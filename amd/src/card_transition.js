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
 * Shows a brief animation of the card moving from one box to another
 * after a question is answered, then navigates to the next question.
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
         * Initialize the card transition animation.
         *
         * @param {string} nextUrl - URL to navigate to after animation.
         * @param {boolean} correct - Whether the answer was correct.
         * @param {number} oldBox - The box the card was in before.
         * @param {number} newBox - The box the card moved to.
         * @param {boolean} isLearned - Whether the card is now learned.
         */
        init: function(nextUrl, correct, oldBox, newBox, isLearned) {
            var fromEl = document.getElementById('lf-box-from');
            var toEl = document.getElementById('lf-box-to');
            var learnedEl = document.getElementById('lf-box-learned');

            // Step 1: After a short delay, animate the source box.
            setTimeout(function() {
                if (fromEl) {
                    fromEl.classList.add('lf-anim-pulse-out');
                }
            }, 300);

            // Step 2: After pulse-out, highlight the target box.
            setTimeout(function() {
                if (fromEl) {
                    fromEl.classList.remove('bg-primary', 'fs-6');
                    fromEl.classList.add('bg-light', 'text-dark', 'border');
                }

                if (isLearned && learnedEl) {
                    learnedEl.classList.remove('bg-light', 'text-dark', 'border');
                    learnedEl.classList.add('bg-success', 'text-white', 'lf-anim-pulse-in');
                } else if (toEl) {
                    toEl.classList.remove('bg-light', 'text-dark', 'border');
                    if (correct) {
                        toEl.classList.add('bg-success', 'text-white', 'lf-anim-pulse-in');
                    } else {
                        toEl.classList.add('bg-warning', 'text-dark', 'lf-anim-pulse-in');
                    }
                }
            }, 800);

            // Step 3: Navigate to next question.
            setTimeout(function() {
                window.location.href = nextUrl;
            }, 1800);
        }
    };
});
