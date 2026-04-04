// AMD module: mod_leitnerflow/quiz_session
// Handles UX enhancements during a quiz attempt:
// - Keyboard shortcut: Enter = submit
// - Auto-focus first answer input
// - Confirm-leave guard

define(['core/log'], function(Log) {
    'use strict';

    return {
        init: function() {
            const form = document.getElementById('leitnerflow-question-form');
            if (!form) {
                return;
            }

            // Auto-focus first interactive element
            const firstInput = form.querySelector('input[type="radio"], input[type="text"], textarea, select');
            if (firstInput) {
                firstInput.focus();
            }

            // Enter key submits the form (when not in textarea)
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                    const btn = document.getElementById('leitnerflow-check-btn');
                    if (btn && !btn.disabled) {
                        btn.click();
                    }
                }
            });

            // Warn before leaving mid-question
            let submitted = false;
            form.addEventListener('submit', function() {
                submitted = true;
            });
            window.addEventListener('beforeunload', function(e) {
                if (!submitted) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            Log.debug('mod_leitnerflow: quiz_session initialised');
        }
    };
});
