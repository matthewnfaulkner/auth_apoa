// This file is part of Moodle - http://moodle.org/ //
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
 * Course format selection handler.
 *
 * @module     auth_apoa/categorychooser
 * @copyright  2022 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      4.0
 */

const Selectors = {
    fields: {
        selector: '[data-categorychooser-field="selector"]',
        updateButton: '[data-categorychooser-field="updateButton"]',
    },
};

/**
 * Initialise the format chooser.
 */
export const init = () => {
    document.querySelector(Selectors.fields.selector).addEventListener('change', e => {
        console.log(e);
        const form = e.target.closest('form');
        const updateButton = form.querySelector(Selectors.fields.updateButton);
        const url = new URL(form.action);
        form.action = url.toString();
        updateButton.click();
    });
};
