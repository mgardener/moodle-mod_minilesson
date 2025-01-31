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

namespace mod_minilesson\local\itemtype;

use mod_minilesson\constants;
use mod_minilesson\utils;
use templatable;
use renderable;

/**
 * Renderable class for a multichoice item in a minilesson activity.
 *
 * @package    mod_minilesson
 * @copyright  2023 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item_multichoice extends item {

    //the item type
    public const ITEMTYPE = constants::TYPE_MULTICHOICE;

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output renderer to be used to render the action bar elements.
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $itemrecord = $this->itemrecord;
        $testitem= new \stdClass();
        $testitem = $this->get_common_elements($testitem);
        $testitem = $this->get_text_answer_elements($testitem);
        $testitem = $this->get_polly_options($testitem);
        $testitem = $this->set_layout($testitem);
        //multichoice also needs sentences if we are listening. Its a bit of double up but we do that here.
        $testitem->sentences = [];
        if($itemrecord->{constants::LISTENORREAD}==constants::LISTENORREAD_LISTEN ||
            $itemrecord->{constants::LISTENORREAD}==constants::LISTENORREAD_LISTENANDREAD) {
            $testitem->audiocontent = 1;
        }
        for ($anumber = 1; $anumber <= constants::MAXANSWERS; $anumber++) {
            if (!empty(trim($itemrecord->{constants::TEXTANSWER . $anumber}))) {
                $sentence = trim($itemrecord->{constants::TEXTANSWER . $anumber});

                $s = new \stdClass();
                $s->index = $anumber - 1;
                $s->indexplusone = $anumber;
                $s->sentence = $sentence;
                $s->length = \core_text::strlen($sentence);

                if($itemrecord->{constants::LISTENORREAD}==constants::LISTENORREAD_LISTEN) {
                    $s->prompt = $this->dottify_text($sentence);
                }else {
                    $s->prompt =$sentence;
                }

                $testitem->sentences[] = $s;
            }
        }

        //multichoice also has a confirm choice option we need to include
        $testitem->confirmchoice = $itemrecord->{constants::CONFIRMCHOICE};

        return $testitem;
    }

}
