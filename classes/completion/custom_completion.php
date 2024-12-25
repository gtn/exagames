<?php
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

declare(strict_types=1);

namespace mod_exagames\completion;

use core_completion\activity_custom_completion;

/**
 * Activity custom completion subclass for the exagames activity.
 *
 * Class for defining mod_exagame's custom completion rules and fetching the completion statuses
 * of the custom completion rules for a given game instance and a user.
 *
 * @package mod_exagames
 */
class custom_completion extends activity_custom_completion {

    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule.
     * @return int The completion state.
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        $userid = $this->userid;
        $gameid = $this->cm->instance;

        if (!$game = $DB->get_record('exagames', ['id' => $gameid])) {
            throw new \moodle_exception('Unable to find a game with id ' . $gameid);
        }

        $postcountparams = ['userid' => $userid, 'gameid' => $gameid];
        if ($rule == 'completionminscore') {
            // check max user's score to pass the activity with the game
            $sql = "SELECT MAX(score)
                           FROM {exagames_scores} gs
                          WHERE gs.userid = :userid
                            AND gs.gameid = :gameid";
            $status = (bool) ((float) $game->completionminscore < (float) $DB->get_field_sql($sql, $postcountparams));
        }

        return $status ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return [
            'completionminscore',
        ];
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        $completionminscore = $this->cm->customdata['customcompletionrules']['completionminscore'] ?? 0;

        return [
            'completionminscore' => get_string('completiondetail:minscore', 'exagames', $completionminscore),
        ];
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [
            'completionview',
            'completionminscore',
            'completionusegrade',
            'completionpassgrade',
        ];
    }
}
