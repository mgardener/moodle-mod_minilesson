<?php

namespace mod_minilesson\local\itemform;

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Forms for minilesson Activity
 *
 * @package    mod_minilesson
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Justin Hunt  http://poodll.com
 */

//why do we need to include this?
require_once($CFG->libdir . '/formslib.php');

use \mod_minilesson\constants;
use \mod_minilesson\utils;

/**
 * Abstract class that item type's inherit from.
 *
 * This is the abstract class that add item type forms must extend.
 *
 * @abstract
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class baseform extends \moodleform {

    /**
     * This is used to identify this itemtype.
     * @var string
     */
    public $type;

    /**
     * The simple string that describes the item type e.g. audioitem, textitem
     * @var string
     */
    public $typestring;

	
    /**
     * An array of options used in the htmleditor
     * @var array
     */
    protected $editoroptions = array();

	/**
     * An array of options used in the filemanager
     * @var array
     */
    protected $filemanageroptions = array();

    /**
     * An array of options used in the filemanager
     * @var array
     */
    protected $moduleinstance = null;


    /**
     * True if this is a standard item of false if it does something special.
     * items are standard items
     * @var bool
     */
    protected $standard = true;

    /**
     * Each item type can and should override this to add any custom elements to
     * the basic form that they want
     */
    public function custom_definition() {}

    /**
     * Used to determine if this is a standard item or a special item
     * @return bool
     */
    public final function is_standard() {
        return (bool)$this->standard;
    }

    /**
     * Add the required basic elements to the form.
     *
     * This method adds the basic elements to the form including title and contents
     * and then calls custom_definition();
     */
    public final function definition() {
        global $CFG;

        $m35 = $CFG->version >= 2018051700;
        $mform = $this->_form;
        $this->editoroptions = $this->_customdata['editoroptions'];
		$this->filemanageroptions = $this->_customdata['filemanageroptions'];
        $this->moduleinstance = $this->_customdata['moduleinstance'];

	
        $mform->addElement('header', 'typeheading', get_string('createaitem', constants::M_COMPONENT, get_string($this->type, constants::M_COMPONENT)));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'itemid');
        $mform->setType('itemid', PARAM_INT);

        if ($this->standard === true) {
            $mform->addElement('hidden', 'type');
            $mform->setType('type', PARAM_TEXT);
			
			$mform->addElement('hidden', 'itemorder');
            $mform->setType('itemorder', PARAM_INT);

            $mform->addElement('text', 'name', get_string('itemtitle', constants::M_COMPONENT), array('size'=>70));
            $mform->setType('name', PARAM_TEXT);
            $mform->addRule('name', get_string('required'), 'required', null, 'client');
            $typelabel =get_string($this->type,constants::M_COMPONENT);
            $mform->setDefault('name', get_string('newitem',constants::M_COMPONENT, $typelabel));


            if($this->moduleinstance->richtextprompt==constants::M_PROMPT_RICHTEXT) {
                $someid = \html_writer::random_id();
                $mform->addElement('editor', constants::TEXTQUESTION . '_editor',
                        get_string('itemcontents', constants::M_COMPONENT),
                        array('id' => $someid, 'wrap' => 'virtual', 'style' => 'width: 100%;', 'rows' => '5'),
                        $this->editoroptions);
                $this->_form->setDefault(constants::TEXTQUESTION . '_editor', array('text' => '', 'format' => FORMAT_HTML));
                $mform->setType(constants::TEXTQUESTION, PARAM_RAW);
            }else{
                //Question instructions
                $mform->addElement('text', constants::TEXTINSTRUCTIONS, get_string('iteminstructions', constants::M_COMPONENT), array('size'=>70));
                $mform->setType(constants::TEXTINSTRUCTIONS, PARAM_RAW);

                //Question text
                $mform->addElement('textarea', constants::TEXTQUESTION, get_string('itemcontents', constants::M_COMPONENT), array('wrap'=>'virtual','style'=>'width: 100%;'));
                $mform->setType(constants::TEXTQUESTION, PARAM_RAW);
                //add layout
                $this->add_layoutoptions();
                switch($this->type) {

                    case constants::TYPE_PAGE:
                        $mform->setDefault(constants::TEXTINSTRUCTIONS,
                            '');
                        break;

                    case constants::TYPE_LISTENREPEAT:
                        $mform->setDefault(constants::TEXTINSTRUCTIONS,
                                get_string('lr_instructions1', constants::M_COMPONENT));
                        break;
                    case constants::TYPE_DICTATIONCHAT:
                        $mform->setDefault(constants::TEXTINSTRUCTIONS,
                                get_string('dc_instructions1', constants::M_COMPONENT));
                        break;
                    case constants::TYPE_SPEECHCARDS:
                        $mform->setDefault(constants::TEXTINSTRUCTIONS,
                                get_string('sc_instructions1', constants::M_COMPONENT));
                        break;
                     case constants::TYPE_DICTATION:
                         $mform->setDefault(constants::TEXTINSTRUCTIONS,
                                 get_string('dictation_instructions1', constants::M_COMPONENT));
                         break;

                     case constants::TYPE_MULTIAUDIO:
                         $mform->setDefault(constants::TEXTINSTRUCTIONS,
                             get_string('multiaudio_instructions1', constants::M_COMPONENT));
                         break;

                    case constants::TYPE_MULTICHOICE:
                        $mform->setDefault(constants::TEXTINSTRUCTIONS,
                            get_string('multichoice_instructions1', constants::M_COMPONENT));
                        break;

                    case constants::TYPE_SHORTANSWER:
                        $mform->setDefault(constants::TEXTINSTRUCTIONS,
                            get_string('shortanswer_instructions1', constants::M_COMPONENT));
                        break;

                    case constants::TYPE_SMARTFRAME:
                        $mform->setDefault(constants::TEXTINSTRUCTIONS,
                            get_string('smartframe_instructions1', constants::M_COMPONENT));
                        break;
                    //listening gapfill
                    case constants::TYPE_LGAPFILL:
                        $mform->setDefault(constants::TEXTINSTRUCTIONS,
                            get_string('lg_instructions1', constants::M_COMPONENT));
                        break;
                    //typing gapfill
                    case constants::TYPE_TGAPFILL:
                        $mform->setDefault(constants::TEXTINSTRUCTIONS,
                            get_string('tg_instructions1', constants::M_COMPONENT));
                        break;
                    //speaking gapfill
                    case constants::TYPE_SGAPFILL:
                        $mform->setDefault(constants::TEXTINSTRUCTIONS,
                            get_string('sg_instructions1', constants::M_COMPONENT));
                        break;

                    //comprehension quiz
                    case constants::TYPE_COMPQUIZ:
                        $mform->setDefault(constants::TEXTINSTRUCTIONS,
                            get_string('listeningquiz_instructions1', constants::M_COMPONENT));
                        break;

                    //button quiz
                    case constants::TYPE_BUTTONQUIZ:
                        $mform->setDefault(constants::TEXTINSTRUCTIONS,
                            get_string('buttonquiz_instructions1', constants::M_COMPONENT));
                        break;
                }

                $togglearray=array();
                $togglearray[] =& $mform->createElement('advcheckbox','addmedia',get_string('addmedia',constants::M_COMPONENT),'');
                $togglearray[] =& $mform->createElement('advcheckbox','addiframe',get_string('addiframe',constants::M_COMPONENT),'');
                $togglearray[] =& $mform->createElement('advcheckbox','addttsaudio',get_string('addttsaudio',constants::M_COMPONENT),'');
                $togglearray[] =& $mform->createElement('advcheckbox','addtextarea',get_string('addtextarea',constants::M_COMPONENT),'');
                $togglearray[] =& $mform->createElement('advcheckbox','addyoutubeclip',get_string('addyoutubeclip',constants::M_COMPONENT),'');
                $togglearray[] =& $mform->createElement('advcheckbox','addttsdialog',get_string('addttsdialog',constants::M_COMPONENT),'');
                $togglearray[] =& $mform->createElement('advcheckbox','addttspassage',get_string('addttspassage',constants::M_COMPONENT),'');
                $mform->addGroup($togglearray, 'togglearray', get_string('mediaprompts', constants::M_COMPONENT), array(' '), false);
                //in the case of page we assume they will want to use some media
                if($this->type== constants::TYPE_PAGE) {
                    $mform->setDefault('addmedia', 1);
                }

                //Question media upload
                $this->add_media_upload(constants::MEDIAQUESTION,get_string('itemmedia',constants::M_COMPONENT));
                if($m35){
                    $mform->hideIf(constants::MEDIAQUESTION, 'addmedia', 'neq', 1);
                }else {
                    $mform->disabledIf(constants::MEDIAQUESTION, 'addmedia', 'neq', 1);
                }


                //Question media iframe
                $mform->addElement('text', constants::MEDIAIFRAME, get_string('itemiframe', constants::M_COMPONENT), array('size'=>100));
                $mform->setType(constants::MEDIAIFRAME, PARAM_RAW);
                if($m35){
                    $mform->hideIf( constants::MEDIAIFRAME,'addiframe','neq', 1);
                }else {
                    $mform->disabledIf( constants::MEDIAIFRAME,'addiframe','neq', 1);
                }


                //Question text to speech
                $mform->addElement('textarea', constants::TTSQUESTION, get_string('itemttsquestion', constants::M_COMPONENT), array('wrap'=>'virtual','style'=>'width: 100%;'));
                $mform->setType(constants::TTSQUESTION, PARAM_RAW);
                $this->add_voiceselect(constants::TTSQUESTIONVOICE,get_string('itemttsquestionvoice',constants::M_COMPONENT));
                $this->add_voiceoptions(constants::TTSQUESTIONOPTION,get_string('choosevoiceoption',constants::M_COMPONENT));
                $mform->addElement('advcheckbox',constants::TTSAUTOPLAY,get_string('autoplay',constants::M_COMPONENT),'');
                if($m35){
                    $mform->hideIf(constants::TTSQUESTION, 'addttsaudio', 'neq', 1);
                    $mform->hideIf(constants::TTSQUESTIONVOICE, 'addttsaudio', 'neq', 1);
                    $mform->hideIf(constants::TTSQUESTIONOPTION, 'addttsaudio', 'neq', 1);
                    $mform->hideIf(constants::TTSAUTOPLAY, 'addttsaudio', 'neq', 1);
                }else {
                    $mform->disabledIf(constants::TTSQUESTION, 'addttsaudio', 'neq', 1);
                    $mform->disabledIf(constants::TTSQUESTIONVOICE, 'addttsaudio', 'neq', 1);
                    $mform->disabledIf(constants::TTSQUESTIONOPTION, 'addttsaudio', 'neq', 1);
                    $mform->disabledIf(constants::TTSAUTOPLAY, 'addttsaudio', 'neq', 1);
                }
                //Question itemtextarea
                $someid = \html_writer::random_id();
                $edoptions = constants::ITEMTEXTAREA_EDOPTIONS;
                //a bug prevents hideif working, but putting it in a group works dandy
                $groupelements= [];
                $groupelements[] = &$mform->createElement('editor', constants::QUESTIONTEXTAREA . '_editor',
                        get_string('itemtextarea', constants::M_COMPONENT),
                        array('id' => $someid, 'wrap' => 'virtual', 'style' => 'width: 100%;', 'rows' => '5'),
                        $edoptions);
                $this->_form->setDefault(constants::QUESTIONTEXTAREA . '_editor', array('text' => '', 'format' => FORMAT_HTML));
                $mform->setType(constants::QUESTIONTEXTAREA, PARAM_RAW);
                $mform->addGroup($groupelements, 'groupelements', get_string('itemtextarea', constants::M_COMPONENT), array(' '), false);
                if($m35){
                    $mform->hideIf('groupelements', 'addtextarea', 'neq', 1);
                   // $mform->hideIf(constants::QUESTIONTEXTAREA. '_editor', 'addtextarea', 'neq', 1);
                }else {
                    $mform->disabledIf('groupelements', 'addtextarea', 'neq', 1);
                   // $mform->disabledIf(constants::QUESTIONTEXTAREA. '_editor', 'addtextarea', 'neq', 1);
                }
            }

            //Question YouTube Clip
            $ytarray=array();
            $ytarray[] =& $mform->createElement('text', constants::YTVIDEOID, get_string('itemytid', constants::M_COMPONENT),  array('size'=>15, 'placeholder'=>"Video ID"));
            $ytarray[] =& $mform->createElement('text', constants::YTVIDEOSTART, get_string('itemytstart', constants::M_COMPONENT),  array('size'=>3,'placeholder'=>"Start"));
            $ytarray[] =& $mform->createElement('html','s - ');
            $ytarray[] =& $mform->createElement('text', constants::YTVIDEOEND, get_string('itemytend', constants::M_COMPONENT),  array('size'=>3,'placeholder'=>"End"));
            $ytarray[] =& $mform->createElement('html','s');

            $mform->addGroup($ytarray, 'ytarray', get_string('ytclipdetails', constants::M_COMPONENT), array(' '), false);
            $mform->setType(constants::YTVIDEOID, PARAM_RAW);
            $mform->setType(constants::YTVIDEOSTART, PARAM_INT);
            $mform->setType(constants::YTVIDEOEND, PARAM_INT);

            if($m35){
                $mform->hideIf('ytarray', 'addyoutubeclip', 'neq', 1);
            }else {
                $mform->disabledIf('ytarray', 'addyoutubeclip', 'neq', 1);
            }

            //Question TTS Dialog

            $ttsdialog_instructions_array=array();
            $ttsdialog_instructions_array[] =& $mform->createElement('static', 'ttsdialog_instructions', null,get_string('ttsdialoginstructions', constants::M_COMPONENT));
            $mform->addGroup($ttsdialog_instructions_array, 'ttsdialog_grp','', array(' '), false);
            //Moodle cant hide static text elements with hideif (why?) , so we wrap it in a group
            //$this->add_static_text('ttsdialog_instructions',null,get_string('ttsdialoginstructions', constants::M_COMPONENT));

            $this->add_voiceselect(constants::TTSDIALOGVOICEA,get_string('ttsdialogvoicea',constants::M_COMPONENT));
            $this->add_voiceselect(constants::TTSDIALOGVOICEB,get_string('ttsdialogvoiceb',constants::M_COMPONENT));
            $this->add_voiceselect(constants::TTSDIALOGVOICEC,get_string('ttsdialogvoicec',constants::M_COMPONENT));
            $mform->addElement('textarea', constants::TTSDIALOG, get_string('ttsdialog', constants::M_COMPONENT), array('wrap'=>'virtual','style'=>'width: 100%;','placeholder'=>'A) Hello&#10;B) Goodbye'));
            $mform->setType(constants::TTSDIALOG, PARAM_RAW);
            $mform->addElement('advcheckbox',constants::TTSDIALOGVISIBLE,get_string('ttsdialogvisible',constants::M_COMPONENT),get_string('ttsdialogvisible_desc', constants::M_COMPONENT));
            $mform->setDefault(constants::TTSDIALOGVISIBLE, 1);

            if($m35){
                $mform->hideIf('ttsdialog_grp', 'addttsdialog', 'neq', 1);
                $mform->hideIf(constants::TTSDIALOGVOICEA, 'addttsdialog', 'neq', 1);
                $mform->hideIf(constants::TTSDIALOGVOICEB, 'addttsdialog', 'neq', 1);
                $mform->hideIf(constants::TTSDIALOGVOICEC, 'addttsdialog', 'neq', 1);
                $mform->hideIf(constants::TTSDIALOGVISIBLE, 'addttsdialog', 'neq', 1);
                $mform->hideIf(constants::TTSDIALOG, 'addttsdialog', 'neq', 1);
            }else {
                $mform->disabledIf('ttsdialog_grp', 'addttsdialog', 'neq', 1);
                $mform->disabledIf(constants::TTSDIALOGVOICEA, 'addttsdialog', 'neq', 1);
                $mform->disabledIf(constants::TTSDIALOGVOICEB, 'addttsdialog', 'neq', 1);
                $mform->disabledIf(constants::TTSDIALOGVOICEC, 'addttsdialog', 'neq', 1);
                $mform->disabledIf(constants::TTSDIALOGVISIBLE, 'addttsdialog', 'neq', 1);
                $mform->disabledIf(constants::TTSDIALOG, 'addttsdialog', 'neq', 1);

            }

            //Question TTS Passage
            $ttspassage_instructions_array=array();
            $ttspassage_instructions_array[] =& $mform->createElement('static', 'ttspassage_instructions', null,get_string('ttspassageinstructions', constants::M_COMPONENT));
            $mform->addGroup($ttspassage_instructions_array, 'ttspassage_grp','', array(' '), false);
            //Moodle cant hide static text elements with hideif (why?) , so we wrap it in a group
            //$this->add_static_text('ttspassage_instructions',null,get_string('ttspassageinstructions', constants::M_COMPONENT));

            $this->add_voiceselect(constants::TTSPASSAGEVOICE,get_string('ttspassagevoice',constants::M_COMPONENT));
            $this->add_voiceoptions(constants::TTSPASSAGESPEED,get_string('ttspassagespeed',constants::M_COMPONENT));
            $mform->addElement('textarea', constants::TTSPASSAGE, get_string('ttspassage', constants::M_COMPONENT), array('wrap'=>'virtual','style'=>'width: 100%;','placeholder'=>''));
            $mform->setType(constants::TTSPASSAGE, PARAM_RAW);

            if($m35){
                $mform->hideIf('ttspassage_grp', 'addttspassage', 'neq', 1);
                $mform->hideIf(constants::TTSPASSAGEVOICE, 'addttspassage', 'neq', 1);
                $mform->hideIf(constants::TTSPASSAGESPEED, 'addttspassage', 'neq', 1);
                $mform->hideIf(constants::TTSPASSAGE, 'addttspassage', 'neq', 1);
            }else {
                $mform->disabledIf('ttspassage_grp', 'addttspassage', 'neq', 1);
                $mform->disabledIf(constants::TTSPASSAGEVOICE, 'addttspassage', 'neq', 1);
                $mform->disabledIf(constants::TTSPASSAGESPEED, 'addttspassage', 'neq', 1);
                $mform->disabledIf(constants::TTSPASSAGE, 'addttspassage', 'neq', 1);

            }
        }

		//visibility
		//$mform->addElement('selectyesno', 'visible', get_string('visible'));
        $mform->addElement('hidden', 'visible',1);
        $mform->setType('visible', PARAM_INT);

        $this->custom_definition();
		
		

		//add the action buttons
        $this->add_action_buttons(get_string('cancel'), get_string('saveitem', constants::M_COMPONENT));

    }

    protected final function add_static_text($name, $label = null,$text='') {

        $this->_form->addElement('static',$name, $label, $text);

    }

    protected final function add_repeating_textboxes($name, $repeatno=5){
        global $DB;

        $additionalfields=1;
        $repeatarray = array();
        $repeatarray[] = $this->_form->createElement('text', $name, get_string($name. 'no', constants::M_COMPONENT));
        //$repeatarray[] = $this->_form->createElement('text', 'limit', get_string('limitno', constants::M_COMPONENT));
        //$repeatarray[] = $this->_form->createElement('hidden', $name . 'id', 0);
/*
        if ($this->_instance){
            $repeatno = $DB->count_records('choice_options', array('choiceid'=>$this->_instance));
            $repeatno += $additionalfields;
        }
*/

        $repeateloptions = array();
        $repeateloptions[$name]['default'] = '';
        //$repeateloptions[$name]['disabledif'] = array('limitanswers', 'eq', 0);
        //$repeateloptions[$name]['rule'] = 'numeric';
        $repeateloptions[$name]['type'] = PARAM_TEXT;

        $repeateloptions[$name]['helpbutton'] = array($name . '_help', constants::M_COMPONENT);
        $this->_form->setType($name, PARAM_CLEANHTML);

       // $this->_form->setType($name .'id', PARAM_INT);

        $this->repeat_elements($repeatarray, $repeatno,
                $repeateloptions, $name .'_repeats', $name . '_add_fields',
                $additionalfields, "add", true);
    }

    protected final function add_showtextpromptoptions($name, $label, $default=constants::TEXTPROMPT_DOTS) {
        $options = utils::fetch_options_textprompt();
        return $this->add_dropdown($name,$label,$options,$default);
    }
    protected final function add_showignorepuncoptions($name, $label, $default=constants::TEXTPROMPT_DOTS) {
        $options = utils::fetch_options_yesno();
        return $this->add_dropdown($name,$label,$options,$default);
    }

    protected final function add_showlistorreadoptions($name, $label, $default=constants::LISTENORREAD_READ) {
        $options = utils::fetch_options_listenorread();
        return $this->add_dropdown($name,$label,$options,$default);
    }

    protected final function add_dropdown($name, $label,$options, $default=false) {

        $this->_form->addElement('select', $name, $label, $options);
        if($default!==false) {
            $this->_form->setDefault($name, $default);
        }

    }

    protected final function add_media_upload($name, $label, $required = false) {
		
		$this->_form->addElement('filemanager',
                           $name,
                           $label,
                           null,
						   $this->filemanageroptions
                           );
		
	}

	protected final function add_media_prompt_upload($label = null, $required = false) {
		return $this->add_media_upload(constants::AUDIOPROMPT,$label,$required);
	}


    /**
     * Convenience function: Adds an response editor
     *
     * @param int $count The count of the element to add
     * @param string $label, null means default
     * @param bool $required
     * @return void
     */
    protected final function add_editorarearesponse($count, $label = null, $required = false) {
        if ($label === null) {
            $label = get_string('response', constants::M_COMPONENT);
        }
        //edoptions = array('noclean'=>true)
        $this->_form->addElement('editor', constants::TEXTANSWER .$count. '_editor', $label, array('rows'=>'4', 'columns'=>'80'), $this->editoroptions);
        $this->_form->setDefault(constants::TEXTANSWER .$count. '_editor', array('text'=>'', 'format'=>FORMAT_MOODLE));
        if ($required) {
            $this->_form->addRule(constants::TEXTANSWER .$count. '_editor', get_string('required'), 'required', null, 'client');
        }
    }

    /**
     * Convenience function: Adds a ext area response
     *
     * @param int $count The count of the element to add
     * @param string $label, null means default
     * @param bool $required
     * @return void
     */
    protected final function add_textarearesponse($count, $label = null, $required = false) {
        if ($label === null) {
            $label = get_string('response', constants::M_COMPONENT);
        }

        $this->_form->addElement('textarea', constants::TEXTANSWER .$count , $label,array('rows'=>'4', 'columns'=>'140', 'style'=>'width: 600px'));
        if ($required) {
            $this->_form->addRule(constants::TEXTANSWER .$count, get_string('required'), 'required', null, 'client');
        }
    }

    /**
     * Convenience function: Adds an response editor
     *
     * @param int $count The count of the element to add
     * @param string $label, null means default
     * @param bool $required
     * @return void
     */
    protected final function add_textboxresponse($count, $label = null, $required = false) {
        if ($label === null) {
            $label = get_string('response', constants::M_COMPONENT);
        }
        $this->_form->addElement('text', constants::TEXTANSWER .$count, $label, array('size'=>'60'));
        $this->_form->setType(constants::TEXTANSWER .$count, PARAM_TEXT);
        if ($required) {
            $this->_form->addRule(constants::TEXTANSWER .$count, get_string('required'), 'required', null, 'client');
        }
    }

    /**
     * Convenience function: Adds layout hint. Width of a single answer
     *
     * @param string $label, null means default
     * @return void
     */
    protected final function add_correctanswer( $label = null) {
        if ($label === null) {
            $label = get_string('correctanswer', constants::M_COMPONENT);
        }
        $options = array();
        $options['1']=1;
        $options['2']=2;
        $options['3']=3;
        $options['4']=4;
        $this->_form->addElement('select', constants::CORRECTANSWER, $label,$options);
        $this->_form->setDefault(constants::CORRECTANSWER, 1);
        $this->_form->setType(constants::CORRECTANSWER, PARAM_INT);
    }

    /**
     * Convenience function: Adds a dropdown list of voices
     *
     * @param string $label, null means default
     * @return void
     */
    protected final function add_layoutoptions() {
        $layoutoptions = [constants::LAYOUT_AUTO=>get_string('layoutauto',constants::M_COMPONENT),
            constants::LAYOUT_HORIZONTAL=>get_string('layouthorizontal',constants::M_COMPONENT),
            constants::LAYOUT_VERTICAL=>get_string('layoutvertical',constants::M_COMPONENT),
            constants::LAYOUT_MAGAZINE=>get_string('layoutmagazine',constants::M_COMPONENT)];
        $name=constants::LAYOUT;
        $this->add_dropdown($name, get_string('chooselayout',constants::M_COMPONENT),$layoutoptions,constants::LAYOUT_AUTO);
    }

    /**
     * Convenience function: Adds a dropdown list of voices
     *
     * @param string $label, null means default
     * @return void
     */
    protected final function add_voiceselect($name, $label = null, $hideif_field=false,$hideif_value=false) {
        global $CFG;
        $showall =true;
        $allvoiceoptions = utils::get_tts_voices($this->moduleinstance->ttslanguage,$showall);
        $somevoiceoptions = utils::get_tts_voices($this->moduleinstance->ttslanguage,!$showall);
        $defaultvoice =array_pop($somevoiceoptions );
        $this->add_dropdown($name, $label,$allvoiceoptions,$defaultvoice);
        if($hideif_field !== false) {
            $m35 = $CFG->version >= 2018051700;
            if ($m35) {
                $this->_form->hideIf($name, $hideif_field, 'eq', $hideif_value);
            } else {
                $this->_form->disabledIf($name, $hideif_field, 'eq', $hideif_value);
            }
        }
    }

    /**
     * Convenience function: Adds a dropdown list of voice options
     *
     * @param string $label, null means default
     * @return void
     */
    protected final function add_voiceoptions($name, $label = null,  $hideif_field=false,$hideif_value=false) {
        global $CFG;
        $voiceoptions = utils::get_tts_options();
        $this->add_dropdown($name, $label,$voiceoptions);
        $m35 = $CFG->version >= 2018051700;
        if($hideif_field !== false) {
            $m35 = $CFG->version >= 2018051700;
            if ($m35) {
                $this->_form->hideIf($name, $hideif_field, 'eq', $hideif_value);
            } else {
                $this->_form->disabledIf($name, $hideif_field, 'eq', $hideif_value);
            }
        }
    }

    /**
     * Convenience function: Adds a dropdown list of voice options
     *
     * @param string $label, null means default
     * @return void
     */
    protected final function add_confirmchoice($name, $label = null) {
        global $CFG;
        if(empty($label)){$label = get_string('confirmchoice_formlabel', constants::M_COMPONENT);}
        $this->_form->addElement('selectyesno', $name,$label);
        $this->_form->setDefault( $name,0);
    }

    /**
     * Convenience function: Adds a dropdown list of tts language
     *
     * @param string $label, null means default
     * @return void
     */
    protected final function add_ttslanguage($name, $label = null) {
        $langoptions = utils::get_lang_options();
        $this->add_dropdown($name, $label,$langoptions);
    }

    /**
     * A function that gets called upon init of this object by the calling script.
     *
     * This can be used to process an immediate action if required. Currently it
     * is only used in special cases by non-standard item types.
     *
     * @return bool
     */
    public function construction_override($itemid,  $minilesson) {
        return true;
    }

    /**
     * Time limit element
     *
     * @param string $name
     * @param string $label
     * @param bool|int $default
     * @return void
     */
    protected final function add_timelimit($name, $label, $default=false) {
        $this->_form->addElement('duration', $name, $label, ['optional' => true, 'defaultunit' => 1]);
        if ($default !== false) {
            $this->_form->setDefault($name, $default);
        }
    }
}
