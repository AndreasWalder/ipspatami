<?php
/**
 * Patami IPS Framework
 *
 * @package IPSPATAMI
 * @version 3.4
 * @link https://bitbucket.org/patami/ipspatami
 *
 * @author Florian Wiethoff <florian.wiethoff@patami.com>
 * @copyright 2017 Florian Wiethoff
 *
 * @license GPL
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * By intentionally submitting any modifications, corrections or derivatives to this work, or any other work intended
 * for use with this Software, to the author, you confirm that you are the copyright holder for those contributions and
 * you grant the author a nonexclusive, worldwide, irrevocable, royalty-free, perpetual, license to use, copy, create
 * derivative works based on those contributions, and sublicense and distribute those contributions and any derivatives
 * thereof.
 */


require_once(__DIR__ . "/../bootstrap.php");


use Patami\IPS\Modules\Module;
use Patami\IPS\Modules\Exceptions\ModuleException;
use Patami\IPS\Objects\Drivers\Drivers;
use Patami\IPS\Objects\Drivers\Driver;
use Patami\IPS\Objects\Drivers\GetSwitchInterface;
use Patami\IPS\Objects\Drivers\GetNumberInterface;
use Patami\IPS\Objects\Drivers\StateInterface;
use Patami\IPS\System\IPS;
use Patami\IPS\System\Color;
use Patami\IPS\System\Semaphore;
use Patami\IPS\Objects\IPSObject;
use Patami\IPS\Objects\Variable;
use Patami\IPS\Objects\IntegerVariable;
use Patami\IPS\Objects\IntegerVariableProfile;
use Patami\IPS\Objects\FloatVariable;
use Patami\IPS\Objects\BooleanVariable;
use Patami\IPS\Objects\Drivers\Exceptions\InvalidObjectException;
use Patami\IPS\Objects\Drivers\Exceptions\StateException;
use Patami\IPS\I18N\Translator;
use Patami\Helpers\Number;
use Patami\IPS\Objects\VariableProfiles;
use Patami\IPS\System\Icons;
use Patami\IPS\Objects\Objects;
use Patami\IPS\Objects\Exceptions\ObjectNotFoundException;
use Patami\IPS\Objects\Link;
use Patami\IPS\Helpers\ArrayHelper;


Translator::AddDir(__DIR__ . DIRECTORY_SEPARATOR . 'translations');


/**
 * PatamiObjectGroup IPS Module.
 * The GUID of this module is {ED1BD621-3BA5-48C1-A016-33E6AECA12F2}.
 * @package IPSPATAMI
 */
class PatamiObjectGroup extends Module
{

    /** The object list is empty. */
    const STATUS_ERROR_NO_OBJECT = 201;
    /** An object is invalid or not supported. */
    const STATUS_ERROR_INVALID_OBJECT = 202;

    /** The object group has been added to itself. */
    const STATUS_ERROR_INVALID_OBJECT_SELF = 203;
    /** The object group has been added to itself. */
    const STATUS_ERROR_INVALID_OBJECT_DUPLICATE = 204;

    /** The minimum number of objects is invalid (too low or too high). */
    const STATUS_ERROR_STATUS_INVALID_MIN_OBJECT_COUNT = 205;

    /** There is a duplicate scene name. */
    const STATUS_ERROR_INVALID_VALUE_RULE_DUPLICATE = 206;

    /** The scene list is empty. */
    const STATUS_ERROR_NO_SCENE = 207;
    /** There is a duplicate scene name. */
    const STATUS_ERROR_INVALID_SCENE_DUPLICATE = 208;

    /** The switch off scene is invalid. */
    const STATUS_ERROR_INVALID_SCENE_SWITCH_OFF = 209;
    /** The switch on scene is invalid. */
    const STATUS_ERROR_INVALID_SCENE_SWITCH_ON = 210;
    /** The alternate switch on scene variable ID is invalid. */
    const STATUS_ERROR_INVALID_SCENE_SWITCH_ON_ALT_VARIABLE_ID = 211;
    /** The alternate switch on scene is invalid. */
    const STATUS_ERROR_INVALID_SCENE_SWITCH_ON_ALT = 212;

    /** Property name for the mode dropdown. */
    const PROPERTY_MODE = 'Mode';

    /** Status mode. */
    const MODE_STATUS = 0;
    /** Status mode. */
    const MODE_VALUE = 1;
    /** Status mode. */
    const MODE_SCENE = 2;

    /** Property name for the object list. */
    const PROPERTY_OBJECTS = 'Objects';

    /** Property name for the status mode dropdown. */
    const PROPERTY_STATUS_MODE = 'StatusMode';

    /** Status mode AND boolean logic. */
    const STATUS_MODE_AND = 0;
    /** Status mode OR boolean logic. */
    const STATUS_MODE_OR = 1;
    /** Status mode count logic. */
    const STATUS_MODE_COUNT = 2;

    /** Property name for the minimum object count. */
    const PROPERTY_STATUS_MIN_OBJECT_COUNT = 'StatusMinObjectCount';

    /** Property name for the changed only checkbox. */
    const PROPERTY_STATUS_CHANGED_ONLY = 'StatusChangedOnly';

    /** Property name for the inverted status checkbox. */
    const PROPERTY_STATUS_INVERTED = 'StatusInverted';

    /** Property name for the value rule list. */
    const PROPERTY_VALUE_RULES = 'ValueRules';

    /** Value rule sum value. */
    const VALUE_RULE_VALUE_SUM = 0;
    /** Value rule average value. */
    const VALUE_RULE_VALUE_AVERAGE = 1;
    /** Value rule minimum value. */
    const VALUE_RULE_VALUE_MINIMUM = 2;
    /** Value rule maximum value. */
    const VALUE_RULE_VALUE_MAXIMUM = 3;
    /** Value rule differenz value. */
    const VALUE_RULE_VALUE_DIFFERENCE = 4;

    /** Value rule equal comparison operator. */
    const VALUE_RULE_COMPARISON_OPERATOR_EQUAL = 0;
    /** Value rule not equal comparison operator. */
    const VALUE_RULE_COMPARISON_OPERATOR_NOT_EQUAL = 1;
    /** Value rule larger than comparison operator. */
    const VALUE_RULE_COMPARISON_OPERATOR_LARGER = 2;
    /** Value rule larger than or equal comparison operator. */
    const VALUE_RULE_COMPARISON_OPERATOR_LARGER_OR_EQUAL = 3;
    /** Value rule smaller comparison operator. */
    const VALUE_RULE_COMPARISON_OPERATOR_SMALLER = 4;
    /** Value rule smaller or equal comparison operator. */
    const VALUE_RULE_COMPARISON_OPERATOR_SMALLER_OR_EQUAL = 5;
    /** Value rule between comparison operator. */
    const VALUE_RULE_COMPARISON_OPERATOR_BETWEEN = 6;
    /** Value rule between or equalcomparison operator. */
    const VALUE_RULE_COMPARISON_OPERATOR_BETWEEN_OR_EQUAL = 7;
    /** Value rule not between comparison operator. */
    const VALUE_RULE_COMPARISON_OPERATOR_NOT_BETWEEN = 8;
    /** Value rule not between or equal comparison operator. */
    const VALUE_RULE_COMPARISON_OPERATOR_NOT_BETWEEN_OR_EQUAL = 9;

    /** Property name for the scene list. */
    const PROPERTY_SCENES = 'Scenes';

    /** Property name for the scene states. */
    const PROPERTY_SCENE_STATES = 'SceneStates';

    /** Property name for the checkbox to create scene UI control element. */
    const PROPERTY_SCENE_UI_ENABLED = 'SceneUIEnabled';

    /** Property name for the checkbox to enable scene switch. */
    const PROPERTY_SCENE_SWITCH_ENABLED = 'SceneSwitchEnabled';
    /** Property name for the scene name to apply when switching off. */
    const PROPERTY_SCENE_SWITCH_OFF_SCENE = 'SceneSwitchOffScene';
    /** Property name for the scene name to apply when switching on. */
    const PROPERTY_SCENE_SWITCH_ON_SCENE = 'SceneSwitchOnScene';
    /** Property name for the checkbox to enable alternate scene when switching on. */
    const PROPERTY_SCENE_SWITCH_ON_ALT_ENABLED = 'SceneSwitchOnAltEnabled';
    /** Property name for the boolean variable ID which determines whether the normal or alternate on scene is applied when switching on */
    const PROPERTY_SCENE_SWITCH_ON_ALT_VARIABLE_ID = 'SceneSwitchOnAltVariableID';
    /** Property name for the alternate scene name when switching on. */
    const PROPERTY_SCENE_SWITCH_ON_ALT_SCENE = 'SceneSwitchOnAltScene';

    /** Ident of the object count status variable. */
    const VAR_OBJECT_COUNT = 'ObjectCount';

    /** Ident of the true object count status variable. */
    const VAR_TRUE_OBJECT_COUNT = 'TrueObjectCount';
    /** Ident of the false object count status variable. */
    const VAR_FALSE_OBJECT_COUNT = 'FalseObjectCount';

    /** Ident of the scene variable. */
    const VAR_SCENE_ID = 'SceneID';

    /** Name format of the scene ID variable profile. */
    const VAR_PROFILE_SCENE_ID_NAME_FORMAT = 'PatamiObjectGroup.%d.SceneID';

    /** Regex to find scene UI variables. */
    const VAR_SCENE_UI_REGEX = '/^SceneUI.*(Apply|Update)Scene$/';
    /** Ident format string of the scene UI apply scene status variables. */
    const VAR_SCENE_UI_APPLY_SCENE_FORMAT = 'SceneUI%sApplyScene';
    /** Ident format string of the scene UI update scene status variables. */
    const VAR_SCENE_UI_UPDATE_SCENE_FORMAT = 'SceneUI%sUpdateScene';

    /** Name of the variable profile for the apply scene button. */
    const VAR_PROFILE_APPLY_SCENE = 'PatamiObjectGroup.ApplyScene';
    /** Name of the variable profile for the apply scene button. */
    const VAR_PROFILE_UPDATE_SCENE = 'PatamiObjectGroup.UpdateScene';

    /** Ident of the value status variable. */
    const VAR_VALUE = 'Value';
    /** Ident of the inverted value status variable. */
    const VAR_INVERTED_VALUE = 'InvertedValue';

    /** Ident of the sum value status variable. */
    const VAR_VALUE_SUM = 'ValueSum';
    /** Ident of the average value status variable. */
    const VAR_VALUE_AVERAGE = 'ValueAverage';
    /** Ident of the minimum value status variable. */
    const VAR_VALUE_MINIMUM = 'ValueMinimum';
    /** Ident of the maximum value status variable. */
    const VAR_VALUE_MAXIMUM = 'ValueMaximum';
    /** Ident of the difference value status variable. */
    const VAR_VALUE_DIFFERENCE = 'ValueDifference';

    /** Regex to find value rule variables. */
    const VAR_VALUE_RULE_REGEX = '/^ValueRule/';
    /** Ident format string of the value rule value status variable. */
    const VAR_VALUE_RULE_VALUE_FORMAT = 'ValueRule%sValue';
    /** Ident format string of the value rule inverted value status variable. */
    const VAR_VALUE_RULE_INVERTED_VALUE_FORMAT = 'ValueRule%sInvertedValue';
    /** Ident format string of the value rule comparison value 1 status variable. */
    const VAR_VALUE_RULE_COMPARISON_VALUE_1_FORMAT = 'ValueRule%sComparisonValue1';
    /** Ident format string of the value rule comparison value 2 status variable. */
    const VAR_VALUE_RULE_COMPARISON_VALUE_2_FORMAT = 'ValueRule%sComparisonValue2';

    /** Name of the IPS object buffer for the configured objects. */
    const OBJECTS_BUFFER_NAME = 'drivers';
    /** Name of the IPS object buffer for the registered messages. */
    const REGISTERED_MESSAGES_BUFFER_NAME = 'registered_messages';

    public function Create()
    {
        // Call the parent method
        parent::Create();

        // Add properties
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_MODE, self::MODE_STATUS);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString(self::PROPERTY_OBJECTS, '');
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_STATUS_MODE, self::STATUS_MODE_OR);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_STATUS_MIN_OBJECT_COUNT, 1);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyBoolean(self::PROPERTY_STATUS_CHANGED_ONLY, true);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyBoolean(self::PROPERTY_STATUS_INVERTED, false);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString(self::PROPERTY_VALUE_RULES, '');
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString(self::PROPERTY_SCENES, '');
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString(self::PROPERTY_SCENE_STATES, '');
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyBoolean(self::PROPERTY_SCENE_UI_ENABLED, true);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyBoolean(self::PROPERTY_SCENE_SWITCH_ENABLED, false);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString(self::PROPERTY_SCENE_SWITCH_OFF_SCENE, '');
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString(self::PROPERTY_SCENE_SWITCH_ON_SCENE, '');
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyBoolean(self::PROPERTY_SCENE_SWITCH_ON_ALT_ENABLED, false);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyInteger(self::PROPERTY_SCENE_SWITCH_ON_ALT_VARIABLE_ID, 0);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->RegisterPropertyString(self::PROPERTY_SCENE_SWITCH_ON_ALT_SCENE, '');
    }

    /**
     * Performs cleanup tasks when the instance is deleted.
     * {@inheritDoc}
     * The PatamiObjectGroup implementation removes the variable profile.
     */
    public function Destroy()
    {
        // Remove variable profiles
        $sceneIdProfileName = sprintf(self::VAR_PROFILE_SCENE_ID_NAME_FORMAT, $this->GetId());
        IPS::DeleteVariableProfile($sceneIdProfileName);

        // Call the parent method
        parent::Destroy();
    }

    protected function GetConfigurationFormData()
    {
        // Call the parent method
        $data = parent::GetConfigurationFormData();

        // Add the mode dropdown
        array_push($data['elements'],
            array(
                'type' => 'Select',
                'name' => self::PROPERTY_MODE,
                'caption' => Translator::Get('patami.patamiobjectgroup.form.mode.label'),
                'options' => array(
                    array(
                        'label' => Translator::Get('patami.patamiobjectgroup.form.mode.status_option'),
                        'value' => self::MODE_STATUS
                    ),
                    array(
                        'label' => Translator::Get('patami.patamiobjectgroup.form.mode.value_option'),
                        'value' => self::MODE_VALUE
                    ),
                    array(
                        'label' => Translator::Get('patami.patamiobjectgroup.form.mode.scene_option'),
                        'value' => self::MODE_SCENE
                    )
                )
            ),
            array(
                'type' => 'Label',
                'label' => Translator::Get('patami.patamiobjectgroup.form.mode.notice')
            )
        );

        // Add the object list
        array_push($data['elements'],
            array(
                'type' => 'Label',
                'label' => ''
            ),
            array(
                'type' => 'List',
                'name' => self::PROPERTY_OBJECTS,
                'caption' => Translator::Get('patami.patamiobjectgroup.form.objects.label'),
                'rowCount' => 10,
                'add' => true,
                'delete' => true,
                'sort' => array(
                    'column' => 'objectLocation',
                    'direction' => 'ascending'
                ),
                'columns' => array(
                    array(
                        'label' => Translator::Get('patami.patamiobjectgroup.form.objects.object_column'),
                        'name' => 'objectLocation',
                        'width' => '381px',
                        'add' => ''
                    ),
                    array(
                        'label' => Translator::Get('patami.patamiobjectgroup.form.objects.object_column'),
                        'name' => 'objectId',
                        'width' => '0px',
                        'add' => 0,
                        'edit' => array(
                            'type' => 'SelectObject'
                        ),
                        'visible' => false
                    ),
                    array(
                        'label' => Translator::Get('patami.patamiobjectgroup.form.objects.status_column'),
                        'name' => 'status',
                        'width' => '300px',
                        'add' => Translator::Get('patami.patamiobjectgroup.form.objects.new_object')
                    )
                ),
                'values' => $this->GetObjectListValues()
            )
        );

        // Get the mode
        $mode = $this->GetMode();

        // Add fields for the "value" mode
        if ($mode == self::MODE_VALUE) {

            // Add the value rules list
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => ''
                ),
                array(
                    'type' => 'List',
                    'name' => self::PROPERTY_VALUE_RULES,
                    'caption' => Translator::Get('patami.patamiobjectgroup.form.value_rules.label'),
                    'rowCount' => 10,
                    'add' => true,
                    'delete' => true,
                    'sort' => array(
                        'column' => 'ruleName',
                        'direction' => 'ascending'
                    ),
                    'columns' => array(
                        array(
                            'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.name_column'),
                            'name' => 'ruleName',
                            'width' => 'auto',
                            'add' => Translator::Get('patami.patamiobjectgroup.form.value_rules.new_rule'),
                            'edit' => array(
                                'type' => 'ValidationTextBox'
                            )
                        ),
                        array(
                            'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.value_column'),
                            'name' => 'value',
                            'width' => '75px',
                            'add' => self::VALUE_RULE_VALUE_AVERAGE,
                            'edit' => array(
                                'type' => 'Select',
                                'caption' => Translator::Get('patami.patamiobjectgroup.form.value_rules.value_column'),
                                'options' => array(
                                    array(
                                        'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.value.sum_option'),
                                        'value' => self::VALUE_RULE_VALUE_SUM
                                    ),
                                    array(
                                        'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.value.average_option'),
                                        'value' => self::VALUE_RULE_VALUE_AVERAGE
                                    ),
                                    array(
                                        'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.value.minimum_option'),
                                        'value' => self::VALUE_RULE_VALUE_MINIMUM
                                    ),
                                    array(
                                        'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.value.maximum_option'),
                                        'value' => self::VALUE_RULE_VALUE_MAXIMUM
                                    ),
                                    array(
                                        'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.value.difference_option'),
                                        'value' => self::VALUE_RULE_VALUE_DIFFERENCE
                                    )
                                )
                            )
                        ),
                        array(
                            'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.comparison_operator_column'),
                            'name' => 'comparisonOperator',
                            'width' => '120px',
                            'add' => self::VALUE_RULE_COMPARISON_OPERATOR_LARGER_OR_EQUAL,
                            'edit' => array(
                                'type' => 'Select',
                                'caption' => Translator::Get('patami.patamiobjectgroup.form.value_rules.comparison_operator_column'),
                                'options' => array(
                                    array(
                                        'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.comparison_operator.equal'),
                                        'value' => self::VALUE_RULE_COMPARISON_OPERATOR_EQUAL
                                    ),
                                    array(
                                        'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.comparison_operator.not_equal'),
                                        'value' => self::VALUE_RULE_COMPARISON_OPERATOR_NOT_EQUAL
                                    ),
                                    array(
                                        'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.comparison_operator.larger'),
                                        'value' => self::VALUE_RULE_COMPARISON_OPERATOR_LARGER
                                    ),
                                    array(
                                        'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.comparison_operator.larger_or_equal'),
                                        'value' => self::VALUE_RULE_COMPARISON_OPERATOR_LARGER_OR_EQUAL
                                    ),
                                    array(
                                        'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.comparison_operator.smaller'),
                                        'value' => self::VALUE_RULE_COMPARISON_OPERATOR_SMALLER
                                    ),
                                    array(
                                        'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.comparison_operator.smaller_or_equal'),
                                        'value' => self::VALUE_RULE_COMPARISON_OPERATOR_SMALLER_OR_EQUAL
                                    ),
                                    array(
                                        'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.comparison_operator.between'),
                                        'value' => self::VALUE_RULE_COMPARISON_OPERATOR_BETWEEN
                                    ),
                                    array(
                                        'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.comparison_operator.not_between'),
                                        'value' => self::VALUE_RULE_COMPARISON_OPERATOR_NOT_BETWEEN
                                    ),
                                    array(
                                        'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.comparison_operator.between_or_equal'),
                                        'value' => self::VALUE_RULE_COMPARISON_OPERATOR_BETWEEN_OR_EQUAL
                                    ),
                                    array(
                                        'label' => Translator::Get('patami.patamiobjectgroup.form.value_rules.comparison_operator.not_between_or_equal'),
                                        'value' => self::VALUE_RULE_COMPARISON_OPERATOR_NOT_BETWEEN_OR_EQUAL
                                    )
                                )
                            )
                        )
                    ),
                    'values' => $this->GetValueRuleValues()
                )
            );

        }

        // Add fields for the "scene" mode
        if ($mode == self::MODE_SCENE) {

            // Add the scene list
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => ''
                ),
                array(
                    'type' => 'List',
                    'name' => self::PROPERTY_SCENES,
                    'caption' => Translator::Get('patami.patamiobjectgroup.form.scenes.label'),
                    'rowCount' => 10,
                    'add' => true,
                    'delete' => true,
                    'sort' => array(
                        'column' => 'sceneName',
                        'direction' => 'ascending'
                    ),
                    'columns' => array(
                        array(
                            'label' => Translator::Get('patami.patamiobjectgroup.form.scenes.name_column'),
                            'name' => 'sceneName',
                            'width' => 'auto',
                            'add' => Translator::Get('patami.patamiobjectgroup.form.scenes.new_scene'),
                            'edit' => array(
                                'type' => 'ValidationTextBox'
                            )
                        )
                    ),
                    'values' => $this->GetSceneValues()
                ),
                array(
                    'type' => 'Label',
                    'label' => ''
                ),
                array(
                    'type' => 'CheckBox',
                    'name' => self::PROPERTY_SCENE_UI_ENABLED,
                    'caption' => Translator::Get('patami.patamiobjectgroup.form.scene_ui.enabled_label')
                ),
                array(
                    'type' => 'Label',
                    'label' => ''
                )
            );

            // Add scene switching configuration fields
            if ($this->IsSceneSwitchingEnabled()) {
                // Switching is enabled
                $options = $this->GetSceneNameDropDownOptions();
                array_push($data['elements'],
                    array(
                        'type' => 'CheckBox',
                        'name' => self::PROPERTY_SCENE_SWITCH_ENABLED,
                        'caption' => Translator::Get('patami.patamiobjectgroup.form.scene_switch.enabled_label_on')
                    ),
                    array(
                        'type' => 'Select',
                        'name' => self::PROPERTY_SCENE_SWITCH_OFF_SCENE,
                        'caption' => Translator::Get('patami.patamiobjectgroup.form.scene_switch.off_scene_label'),
                        'options' => $options
                    ),
                    array(
                        'type' => 'Select',
                        'name' => self::PROPERTY_SCENE_SWITCH_ON_SCENE,
                        'caption' => Translator::Get('patami.patamiobjectgroup.form.scene_switch.on_scene_label'),
                        'options' => $options
                    )
                );
                // Add alternate switch on configuration fields
                if ($this->IsAlternateSwitchOnSceneEnabled()) {
                    // Alternate switch on scene is enabled
                    array_push($data['elements'],
                        array(
                            'type' => 'CheckBox',
                            'name' => self::PROPERTY_SCENE_SWITCH_ON_ALT_ENABLED,
                            'caption' => Translator::Get('patami.patamiobjectgroup.form.scene_switch.alternate_enabled_label_on')
                        ),
                        array(
                            'type' => 'Select',
                            'name' => self::PROPERTY_SCENE_SWITCH_ON_ALT_SCENE,
                            'caption' => '',
                            'options' => $options
                        ),
                        array(
                            'type' => 'SelectVariable',
                            'name' => self::PROPERTY_SCENE_SWITCH_ON_ALT_VARIABLE_ID,
                            'caption' => ''
                        )
                    );
                    // Add the variable location if specified
                    $alternateSwitchOnSceneVariableId = $this->GetAlternateSwitchOnSceneVariableId();
                    if ($alternateSwitchOnSceneVariableId) {
                        array_push($data['elements'],
                            array(
                                'type' => 'Label',
                                'label' => IPS::GetLocation($alternateSwitchOnSceneVariableId)
                            )
                        );
                    }
                } else {
                    // Alternate switch on scene is disabled
                    array_push($data['elements'],
                        array(
                            'type' => 'CheckBox',
                            'name' => self::PROPERTY_SCENE_SWITCH_ON_ALT_ENABLED,
                            'caption' => Translator::Get('patami.patamiobjectgroup.form.scene_switch.alternate_enabled_label')
                        )
                    );
                }
            } else {
                // Switching is disabled
                array_push($data['elements'],
                    array(
                        'type' => 'CheckBox',
                        'name' => self::PROPERTY_SCENE_SWITCH_ENABLED,
                        'caption' => Translator::Get('patami.patamiobjectgroup.form.scene_switch.enabled_label')
                    )
                );
            }

            // Only show the scene management actions if the instance is valid
            if ($this->IsValid()) {

                // Add the scene management actions
                array_unshift($data['actions'],
                    array(
                        'type' => 'Label',
                        'label' => Translator::Get('patami.patamiobjectgroup.form.actions.scene.label')
                    ),
                    array(
                        'type' => 'Select',
                        'name' => 'scene',
                        'caption' => Translator::Get('patami.patamiobjectgroup.form.actions.scene.dropdown_label'),
                        'options' => $this->GetSceneNameDropDownOptions()
                    ),
                    array(
                        'type' => 'Button',
                        'label' => Translator::Get('patami.patamiobjectgroup.form.actions.scene.apply_button'),
                        'onClick' => 'POG_ApplySceneAction($id, $scene);'
                    ),
                    array(
                        'type' => 'Button',
                        'label' => Translator::Get('patami.patamiobjectgroup.form.actions.scene.update_button'),
                        'onClick' => 'POG_UpdateSceneAction($id, $scene);'
                    ),
                    array(
                        'type' => 'Label',
                        'label' => ''
                    ),
                    array(
                        'type' => 'Label',
                        'label' => Translator::Get('patami.patamiobjectgroup.form.actions.scene.cleanup_label')
                    ),
                    array(
                        'type' => 'Button',
                        'label' => Translator::Get('patami.patamiobjectgroup.form.actions.scene.cleanup_button'),
                        'onClick' => 'POG_CleanupSceneStatesAction($id);'
                    ),
                    array(
                        'type' => 'Label',
                        'label' => ''
                    ),
                    array(
                        'type' => 'Label',
                        'label' => Translator::Get('patami.patamiobjectgroup.form.actions.scene.create_links_label')
                    ),
                    array(
                        'type' => 'Button',
                        'label' => Translator::Get('patami.patamiobjectgroup.form.actions.scene.create_links_button'),
                        'onClick' => 'POG_CreateLinksAction($id);'
                    )
                );

            }

        }

        // Add fields for the "status" or the "scene" mode
        if ($mode == self::MODE_STATUS || $mode == self::MODE_SCENE) {

            // Add the status mode dropdown
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => ''
                ),
                array(
                    'type' => 'Label',
                    'label' => Translator::Get('patami.patamiobjectgroup.form.status.mode_label')
                ),
                array(
                    'type' => 'Select',
                    'name' => self::PROPERTY_STATUS_MODE,
                    'caption' => '',
                    'options' => array(
                        array(
                            'label' => Translator::Get('patami.patamiobjectgroup.form.status.mode_and_option'),
                            'value' => self::STATUS_MODE_AND
                        ),
                        array(
                            'label' => Translator::Get('patami.patamiobjectgroup.form.status.mode_or_option'),
                            'value' => self::STATUS_MODE_OR
                        ),
                        array(
                            'label' => Translator::Get('patami.patamiobjectgroup.form.status.mode_count_option'),
                            'value' => self::STATUS_MODE_COUNT
                        )
                    )
                )
            );

            // Get the status mode
            $statusMode = $this->GetStatusMode();

            // Add the object count field
            if ($statusMode == self::STATUS_MODE_COUNT) {
                array_push($data['elements'],
                    array(
                        'type' => 'NumberSpinner',
                        'name' => self::PROPERTY_STATUS_MIN_OBJECT_COUNT,
                        'caption' => Translator::Get('patami.patamiobjectgroup.form.status.min_object_count_label')
                    )
                );
            }

            // Add a notice
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => Translator::Get('patami.patamiobjectgroup.form.status.notice')
                )
            );

            // Add changed only checkbox
            array_push($data['elements'],
                array(
                    'type' => 'Label',
                    'label' => ''
                ),
                array(
                    'type' => 'CheckBox',
                    'name' => self::PROPERTY_STATUS_CHANGED_ONLY,
                    'caption' => Translator::Get('patami.patamiobjectgroup.form.status.changed_only_label')
                )
            );

            // Add invert checkbox
            array_push($data['elements'],
                array(
                    'type' => 'CheckBox',
                    'name' => self::PROPERTY_STATUS_INVERTED,
                    'caption' => Translator::Get('patami.patamiobjectgroup.form.status.inverted_label')
                )
            );

        }

        // Add status messages
        array_push($data['status'],
            array(
                'code' => self::STATUS_ERROR_NO_OBJECT,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiobjectgroup.form.status.no_object')
            ),
            array(
                'code' => self::STATUS_ERROR_INVALID_OBJECT,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiobjectgroup.form.status.invalid_object')
            ),
            array(
                'code' => self::STATUS_ERROR_INVALID_OBJECT_SELF,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiobjectgroup.form.status.invalid_object_self')
            ),
            array(
                'code' => self::STATUS_ERROR_INVALID_OBJECT_DUPLICATE,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiobjectgroup.form.status.invalid_object_duplicate')
            ),
            array(
                'code' => self::STATUS_ERROR_STATUS_INVALID_MIN_OBJECT_COUNT,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiobjectgroup.form.status.invalid_min_object_count')
            ),
            array(
                'code' => self::STATUS_ERROR_INVALID_VALUE_RULE_DUPLICATE,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiobjectgroup.form.status.invalid_value_rule_duplicate')
            ),
            array(
                'code' => self::STATUS_ERROR_NO_SCENE,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiobjectgroup.form.status.no_scene')
            ),
            array(
                'code' => self::STATUS_ERROR_INVALID_SCENE_DUPLICATE,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiobjectgroup.form.status.invalid_scene_duplicate')
            ),
            array(
                'code' => self::STATUS_ERROR_INVALID_SCENE_SWITCH_OFF,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiobjectgroup.form.status.invalid_scene_switch_off')
            ),
            array(
                'code' => self::STATUS_ERROR_INVALID_SCENE_SWITCH_ON,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiobjectgroup.form.status.invalid_scene_switch_on')
            ),
            array(
                'code' => self::STATUS_ERROR_INVALID_SCENE_SWITCH_ON_ALT_VARIABLE_ID,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiobjectgroup.form.status.invalid_scene_switch_on_alt_variable_id')
            ),
            array(
                'code' => self::STATUS_ERROR_INVALID_SCENE_SWITCH_ON_ALT,
                'icon' => 'error',
                'caption' => Translator::Get('patami.patamiobjectgroup.form.status.invalid_scene_switch_on_alt')
            )
        );

        return $data;
    }

    protected function Configure()
    {
        // Initialize data structures
        $drivers = array();
        $messages = array();

        try {

            // Get the object ids
            $objectIds = $this->GetObjectIds();
            $objectCount = count($objectIds);

            // Get the mode
            $mode = $this->GetMode();

            // Add variable profiles
            $transparent = new Color(Color::TRANSPARENT);
            $applySceneProfile = new IntegerVariableProfile(self::VAR_PROFILE_APPLY_SCENE);
            $applySceneProfile->AddAssociationByValue(0, Translator::Get('patami.patamiobjectgroup.vars.scene_ui.apply'), Icons::STARS, $transparent);
            $updateSceneProfile = new IntegerVariableProfile(self::VAR_PROFILE_UPDATE_SCENE);
            $updateSceneProfile->AddAssociationByValue(0, Translator::Get('patami.patamiobjectgroup.vars.scene_ui.update'), Icons::STARS, $transparent);

            // Add status variables
            $index = 0;
            $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_OBJECT_COUNT, Translator::Get('patami.patamiobjectgroup.vars.object_count')));
            $variable->Set($objectCount)->SetPosition($index++)->SetIcon(Icons::GAUGE);

            // Add mode specific variables and profiles
            if ($mode == self::MODE_STATUS || $mode == self::MODE_SCENE) {
                // Status or scene mode
                VariableProfiles::CreateOnOff();
                $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_TRUE_OBJECT_COUNT, Translator::Get('patami.patamiobjectgroup.vars.true_object_count')));
                $variable->SetPosition($index++)->SetIcon(Icons::GAUGE);
                $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_FALSE_OBJECT_COUNT, Translator::Get('patami.patamiobjectgroup.vars.false_object_count')));
                $variable->SetPosition($index++)->SetIcon(Icons::GAUGE);
                $variable = new BooleanVariable($this->RegisterVariableBooleanEx(self::VAR_VALUE, Translator::Get('patami.patamiobjectgroup.vars.value'), VariableProfiles::TYPE_PATAMI_ONOFF));
                $variable->SetPosition($index++);
                $variable = new BooleanVariable($this->RegisterVariableBooleanEx(self::VAR_INVERTED_VALUE, Translator::Get('patami.patamiobjectgroup.vars.inverted_value'), VariableProfiles::TYPE_PATAMI_ONOFF));
                $variable->SetPosition($index++);
                // Remove value rule variables
                $this->RemoveObsoleteValueRuleStatusVariables();
            } else {
                // Value mode
                // Remove variables of other modes
                /** @noinspection PhpUndefinedMethodInspection */
                $this->UnregisterVariable(self::VAR_TRUE_OBJECT_COUNT);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->UnregisterVariable(self::VAR_FALSE_OBJECT_COUNT);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->UnregisterVariable(self::VAR_VALUE);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->UnregisterVariable(self::VAR_INVERTED_VALUE);
            }
            $sceneIdProfileName = sprintf(self::VAR_PROFILE_SCENE_ID_NAME_FORMAT, $this->GetId());
            if ($mode == self::MODE_STATUS || $mode == self::MODE_VALUE || ! $this->IsSceneUIEnabled()) {
                // Status or value mode or scene UI elements not enabled
                // Remove variables of other modes
                /** @noinspection PhpUndefinedMethodInspection */
                $this->UnregisterVariable(self::VAR_SCENE_ID);
                // Remove variable profiles
                IPS::DeleteVariableProfile($sceneIdProfileName);
                // Remove obsolete scene UI controls
                $this->RemoveObsoleteSceneUIStatusVariables();
            } else {
                // Scene mode
                // Create the variable profile for the scenes
                $transparent = new Color(Color::TRANSPARENT);
                $sceneNames = $this->GetSceneNames();
                $profile = new IntegerVariableProfile($sceneIdProfileName);
                $profile->SetIcon(Icons::STARS)->DeleteAssociations();
                $value = 0;
                foreach ($sceneNames as $sceneName) {
                    $profile->AddAssociationByValue($value, $sceneName, '', $transparent);
                    $value++;
                }
                // Create the variable for the scene
                $variable = new IntegerVariable($this->RegisterVariableIntegerEx(self::VAR_SCENE_ID, Translator::Get('patami.patamiobjectgroup.vars.scene_id'), $sceneIdProfileName));
                $variable->SetPosition($index++);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->EnableAction(self::VAR_SCENE_ID);
            }
            if ($mode == self::MODE_VALUE) {
                // Value mode
                $variable = new FloatVariable($this->RegisterVariableFloatEx(self::VAR_VALUE_SUM, Translator::Get('patami.patamiobjectgroup.vars.value_sum')));
                $variable->SetPosition($index++);
                $variable = new FloatVariable($this->RegisterVariableFloatEx(self::VAR_VALUE_AVERAGE, Translator::Get('patami.patamiobjectgroup.vars.value_average')));
                $variable->SetPosition($index++);
                $variable = new FloatVariable($this->RegisterVariableFloatEx(self::VAR_VALUE_MINIMUM, Translator::Get('patami.patamiobjectgroup.vars.value_minimum')));
                $variable->SetPosition($index++);
                $variable = new FloatVariable($this->RegisterVariableFloatEx(self::VAR_VALUE_MAXIMUM, Translator::Get('patami.patamiobjectgroup.vars.value_maximum')));
                $variable->SetPosition($index++);
                $variable = new FloatVariable($this->RegisterVariableFloatEx(self::VAR_VALUE_DIFFERENCE, Translator::Get('patami.patamiobjectgroup.vars.value_difference')));
                $variable->SetPosition($index++);
            } else {
                // Status or scene mode
                // Remove variables of other modes
                /** @noinspection PhpUndefinedMethodInspection */
                $this->UnregisterVariable(self::VAR_VALUE_SUM);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->UnregisterVariable(self::VAR_VALUE_AVERAGE);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->UnregisterVariable(self::VAR_VALUE_MINIMUM);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->UnregisterVariable(self::VAR_VALUE_MAXIMUM);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->UnregisterVariable(self::VAR_VALUE_DIFFERENCE);
            }

            // Check if the list is empty
            if ($objectCount == 0) {
                $this->Debug('Object Validation', 'The object list is empty');
                throw new ModuleException('', self::STATUS_ERROR_NO_OBJECT);
            }

            // Check if there are duplicate entries
            if (count(array_unique($objectIds)) != $objectCount) {
                $this->Debug('Object Validation', 'At least one object is duplicate');
                throw new ModuleException('', self::STATUS_ERROR_INVALID_OBJECT_DUPLICATE);
            }

            // Check mode specific properties
            if ($mode == self::MODE_STATUS || $mode == self::MODE_SCENE) {
                // Status mode
                $statusMode = $this->GetStatusMode();
                if ($statusMode == self::STATUS_MODE_COUNT) {
                    // Minimum number of objects
                    $minObjectCount = $this->GetStatusMinObjectCount();
                    if ($minObjectCount < 1 || $minObjectCount > $objectCount) {
                        $this->Debug('Status Validation', 'The minimum number of objects is invalid');
                        throw new ModuleException('', self::STATUS_ERROR_STATUS_INVALID_MIN_OBJECT_COUNT);
                    }
                }
            }

            // Get the interface name
            $interfaceName = $this->GetDriverInterfaceNames();

            // Define lambda function to add messages to the data structure
            $addMessage = function (&$messages, $objectId, $messageId) {
                $key = sprintf('%d_%d', $objectId, $messageId);
                $messages[$key] = array(
                    'objectId' => $objectId,
                    'messageId' => $messageId
                );
            };

            // Loop through all mappings, check the objects and collect the monitored objects
            $index = 1;
            foreach ($objectIds as $objectId) {
                // Check if an object ID has been configured
                if ($objectId == 0) {
                    $this->Debug('Object Validation', sprintf('No object specified in entry %d', $index));
                    throw new ModuleException('', self::STATUS_ERROR_INVALID_OBJECT);
                }
                // Check if the object has been added to itself
                if ($objectId == $this->GetId()) {
                    $this->Debug('Object Validation', sprintf('Must not add self in entry %d', $index));
                    throw new ModuleException('', self::STATUS_ERROR_INVALID_OBJECT_SELF);
                }
                // Listen for instance reloads
                $this->ListenForInstanceReloads($objectId);
                try {
                    // Detect the driver
                    /** @var Driver $driver */
                    $driver = Drivers::AutoDetect($objectId, $interfaceName);
                    // Log information about the object
                    $this->Debug($objectId, sprintf('Name: %s', utf8_decode(IPS::GetLocation($objectId))));
                    $this->Debug($objectId, sprintf('Driver: %s', $driver->GetName()));
                    // Remember the driver
                    $drivers[$objectId] = $driver->GetName();
                    // Register message to get notified if the object is being deleted
                    $addMessage($messages, $objectId, OM_UNREGISTER);
                    // Get the monitored objects
                    $monitoredObjects = $driver->GetMonitoredObjects();
                    /** @var IPSObject $monitoredObject */
                    // Loop through the monitored objects
                    foreach ($monitoredObjects as $monitoredObject) {
                        $monitoredObjectId = $monitoredObject->GetId();
                        // Log information about the monitored object
                        $this->Debug($objectId, sprintf(
                            'Monitored object: %d (%s)',
                            $monitoredObjectId,
                            utf8_decode($monitoredObject->GetLocation())
                        ));
                        // Register message to get notified if the monitored object changes
                        if ($monitoredObject instanceof Variable) {
                            // Listen for variable updates
                            $addMessage($messages, $monitoredObjectId, VM_UPDATE);
                        } else {
                            // We don't know how to handle this object
                            $this->Debug($objectId, sprintf('Cannot monitor %d (not supported)', $monitoredObjectId));
                        }
                    }
                } catch (InvalidObjectException $e) {
                    $this->Debug($objectId, 'No matching driver found');
                    throw new ModuleException('', self::STATUS_ERROR_INVALID_OBJECT);
                }
                $index++;
            }

            // Call mode specific method
            if ($mode == self::MODE_STATUS) {
                $this->ConfigureModeStatus();
            }
            if ($mode == self::MODE_VALUE) {
                $variableIds = $this->ConfigureModeValue();
                foreach ($variableIds as $variableId) {
                    $addMessage($messages, $variableId, VM_UPDATE);
                }
            }
            if ($mode == self::MODE_SCENE) {
                $this->ConfigureModeScene();
            }

            // Call the parent method
            parent::Configure();

        } catch (ModuleException $e) {

            // Set the instance statis
            /** @noinspection PhpUndefinedMethodInspection */
            $this->SetStatus($e->getCode());

        } finally {

            // Enter the semaphore to make sure the buffer is consistent
            $semaphore = new Semaphore(sprintf('%d_%s', $this->GetId(), self::REGISTERED_MESSAGES_BUFFER_NAME));
            $semaphore->Enter();

            try {

                // Load the registered messages
                /** @noinspection PhpUndefinedMethodInspection */
                $data = $this->GetBuffer(self::REGISTERED_MESSAGES_BUFFER_NAME);
                $oldMessages = @json_decode($data, true);
                if (is_null($oldMessages)) {
                    $oldMessages = array();
                }

                // Unregister obsolete messages
                $obsoleteMessages = array_diff_key($oldMessages, $messages);
                foreach ($obsoleteMessages as $data) {
                    $objectId = $data['objectId'];
                    $messageId = $data['messageId'];
                    // Log it
                    $this->Debug($objectId, sprintf('Unregistering message %d', $messageId));
                    // Remove it
                    /** @noinspection PhpUndefinedMethodInspection */
                    $this->UnregisterMessage($objectId, $messageId);
                }

                // Register new messages
                $newMessages = array_diff_key($messages, $oldMessages);
                foreach ($newMessages as $data) {
                    $objectId = $data['objectId'];
                    $messageId = $data['messageId'];
                    // Log it
                    $this->Debug($objectId, sprintf('Registering message %d', $messageId));
                    // Remove it
                    /** @noinspection PhpUndefinedMethodInspection */
                    $this->RegisterMessage($objectId, $messageId);
                }

                // Remember the objects and the drivers
                /** @noinspection PhpUndefinedMethodInspection */
                $this->SetBuffer(self::OBJECTS_BUFFER_NAME, json_encode($drivers));

                // Remember the registered messages
                /** @noinspection PhpUndefinedMethodInspection */
                $this->SetBuffer(self::REGISTERED_MESSAGES_BUFFER_NAME, json_encode($messages));

            } finally {

                // Leave the semaphore
                $semaphore->Leave();

                // Update the status variables
                $this->UpdateStatus();

            }

        }

    }

    /**
     * Performs actions required when the configuration of this instance was saved and the mode is "status".
     */
    protected function ConfigureModeStatus()
    {
        // Set the summary
        /** @noinspection PhpUndefinedMethodInspection */
        $this->SetSummary(Translator::Get('patami.patamiobjectgroup.form.mode.status_option'));

        // Disable the action of the status variable
        /** @noinspection PhpUndefinedMethodInspection */
        $this->DisableAction(self::VAR_VALUE);
    }

    /**
     * Performs actions required when the configuration of this instance was saved and the mode is "value".
     * @return array List of IPS variable object IDs for the configurable status variables.
     * @throws ModuleException if there are duplicate rule names.
     */
    protected function ConfigureModeValue()
    {
        // Set the summary
        /** @noinspection PhpUndefinedMethodInspection */
        $this->SetSummary(Translator::Get('patami.patamiobjectgroup.form.mode.value_option'));

        // Get the rule names
        $ruleNames = $this->GetValueRuleNames();
        $ruleCount = count($ruleNames);

        // Check if there are duplicate entries
        if (count(array_unique($ruleNames)) != $ruleCount) {
            $this->Debug('Value Rule Validation', 'At least one rule name is duplicate');
            throw new ModuleException('', self::STATUS_ERROR_INVALID_VALUE_RULE_DUPLICATE);
        }

        // Add value rule variables
        $index = 100;
        $idents = $variableIds = array();
        $rules = $this->GetValueRules();
        foreach ($rules as $rule) {
            $name = $rule['ruleName'];
            $operator = $rule['comparisonOperator'];
            // Rule result status variable
            /** @var Variable $variable */
            $idents[] = $ident = $this->GetValueRuleValueIdent($name);
            $label = Translator::Format('patami.patamiobjectgroup.vars.value_rule_value', $name);
            $variable = new BooleanVariable($this->RegisterVariableBooleanEx($ident, $label));
            $variable->SetPosition($index++);
            // Inverted rule result status variable
            $idents[] = $ident = $this->GetValueRuleInvertedValueIdent($name);
            $label = Translator::Format('patami.patamiobjectgroup.vars.value_rule_inverted_value', $name);
            $variable = new BooleanVariable($this->RegisterVariableBooleanEx($ident, $label));
            $variable->SetPosition($index++);
            // Comparison value variables
            if (Number::IsInRange($operator, self::VALUE_RULE_COMPARISON_OPERATOR_BETWEEN, self::VALUE_RULE_COMPARISON_OPERATOR_NOT_BETWEEN_OR_EQUAL)) {
                // Between operators (two comparison values)
                // Lower bound
                $idents[] = $ident = $this->GetValueRuleComparisonValue1Ident($name);
                $label = Translator::Format('patami.patamiobjectgroup.vars.value_rule_comparison_value_lower', $name);
                $variable = new FloatVariable($this->RegisterVariableFloatEx($ident, $label));
                $variableIds[] = $variable->GetId();
                $variable->SetPosition($index++);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->EnableAction($ident);
                // Upper bound
                $idents[] = $ident = $this->GetValueRuleComparisonValue2Ident($name);
                $label = Translator::Format('patami.patamiobjectgroup.vars.value_rule_comparison_value_upper', $name);
                $variable = new FloatVariable($this->RegisterVariableFloatEx($ident, $label));
                $variableIds[] = $variable->GetId();
                $variable->SetPosition($index++);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->EnableAction($ident);
            } else {
                // Other operators (one comparison value)
                $idents[] = $ident = $this->GetValueRuleComparisonValue1Ident($name);
                $label = Translator::Format('patami.patamiobjectgroup.vars.value_rule_comparison_value', $name);
                $variable = new FloatVariable($this->RegisterVariableFloatEx($ident, $label));
                $variableIds[] = $variable->GetId();
                $variable->SetPosition($index++);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->EnableAction($ident);
            }
        }

        // Remove obsolete value rule variables
        $this->RemoveObsoleteValueRuleStatusVariables($idents);

        // Return the variable IDs
        return $variableIds;
    }

    /**
     * Performs actions required when the configuration of this instance was saved and the mode is "scene".
     * @throws ModuleException if there are no or duplicate scene names.
     */
    protected function ConfigureModeScene()
    {
        // Set the summary
        /** @noinspection PhpUndefinedMethodInspection */
        $this->SetSummary(Translator::Get('patami.patamiobjectgroup.form.mode.scene_option'));

        // Get the scene names
        $sceneNames = $this->GetSceneNames();
        $sceneCount = count($sceneNames);

        // Check if the list is empty
        if ($sceneCount == 0) {
            $this->Debug('Scene Validation', 'The scene list is empty');
            throw new ModuleException('', self::STATUS_ERROR_NO_SCENE);
        }

        // Check if there are duplicate entries
        if (count(array_unique($sceneNames)) != $sceneCount) {
            $this->Debug('Scene Validation', 'At least one scene is duplicate');
            throw new ModuleException('', self::STATUS_ERROR_INVALID_SCENE_DUPLICATE);
        }

        // Check if scene UI controls should be created
        $idents = array();
        if ($this->IsSceneUIEnabled()) {
            // Add the scene UI control variables
            $index = 100;
            $rules = $this->GetValueRules();
            foreach ($sceneNames as $sceneName) {
                // Apply control
                /** @var Variable $variable */
                $idents[] = $ident = $this->GetSceneUIApplySceneIdent($sceneName);
                $variable = new IntegerVariable($this->RegisterVariableIntegerEx($ident, $sceneName, self::VAR_PROFILE_APPLY_SCENE));
                $variable->SetPosition($index++)->Set(0);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->EnableAction($ident);
                // Update control
                $idents[] = $ident = $this->GetSceneUIUpdateSceneIdent($sceneName);
                $variable = new IntegerVariable($this->RegisterVariableIntegerEx($ident, $sceneName, self::VAR_PROFILE_UPDATE_SCENE));
                $variable->SetPosition($index++)->Set(0);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->EnableAction($ident);
            }
        }
        // Remove obsolete scene UI controls
        $this->RemoveObsoleteSceneUIStatusVariables($idents);

        // Check if scene switching is enabled
        if ($this->IsSceneSwitchingEnabled()) {
            // Check the switch off scene
            $offSceneName = $this->GetSceneSwitchOffScene();
            if (! in_array($offSceneName, $sceneNames)) {
                $this->Debug('Switch Off Scene Validation', sprintf('The scene %s is invalid', $offSceneName));
                throw new ModuleException('', self::STATUS_ERROR_INVALID_SCENE_SWITCH_OFF);
            }
            // Check the switch on scene
            $onSceneName = $this->GetSceneSwitchOnScene();
            if (! in_array($onSceneName, $sceneNames)) {
                $this->Debug('Switch On Scene Validation', sprintf('The scene %s is invalid', $onSceneName));
                throw new ModuleException('', self::STATUS_ERROR_INVALID_SCENE_SWITCH_ON);
            }
            // Check if the alternate switch on scene is enabled
            if ($this->IsAlternateSwitchOnSceneEnabled()) {
                // Check the alternate switch on scene
                $alternateOnSceneName = $this->GetAlternateSwitchOnScene();
                if (! in_array($alternateOnSceneName, $sceneNames)) {
                    $this->Debug('Alternate Switch On Scene Validation', sprintf('The scene %s is invalid', $alternateOnSceneName));
                    throw new ModuleException('', self::STATUS_ERROR_INVALID_SCENE_SWITCH_ON_ALT);
                }
                // Check the alternate switch on variable
                $alternateOnSceneVariableId = $this->GetAlternateSwitchOnSceneVariableId();
                if ($alternateOnSceneVariableId) {
                    $alternateOnSceneVariable = Objects::GetByID($alternateOnSceneVariableId);
                    if (! $alternateOnSceneVariable instanceof BooleanVariable) {
                        $this->Debug('Alternate Switch On Scene Validation', 'The variable is invalid');
                        throw new ModuleException('', self::STATUS_ERROR_INVALID_SCENE_SWITCH_ON_ALT_VARIABLE_ID);
                    }
                } else {
                    $this->Debug('Alternate Switch On Scene Validation', 'The variable is not configured');
                    throw new ModuleException('', self::STATUS_ERROR_INVALID_SCENE_SWITCH_ON_ALT_VARIABLE_ID);
                }
            }
            // Enable the action of the status variable
            /** @noinspection PhpUndefinedMethodInspection */
            $this->EnableAction(self::VAR_VALUE);
        } else {
            // Disable the action of the status variable
            /** @noinspection PhpUndefinedMethodInspection */
            $this->DisableAction(self::VAR_VALUE);
        }

    }

    /**
     * Returns the mode of the instance.
     * @return int Mode of the instance.
     */
    public function GetMode()
    {
        // Return the mode
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_MODE);
    }

    /**
     * Returns the FCINs of the interfaces used for driver auto-detection.
     * @return string|array|null FQIN of the interfaces.
     */
    protected function GetDriverInterfaceNames()
    {
        // Get the mode
        $mode = $this->GetMode();

        // Return the FQINs
        switch ($mode) {
            case self::MODE_STATUS:
                return 'Patami\\IPS\\Objects\\Drivers\\GetSwitchInterface';
            case self::MODE_VALUE:
                return 'Patami\\IPS\\Objects\\Drivers\\GetNumberInterface';
            case self::MODE_SCENE:
                return array(
                    'Patami\\IPS\\Objects\\Drivers\\StateInterface',
                    'Patami\\IPS\\Objects\\Drivers\\GetSwitchInterface'
                );
            default:
                return null;
        }
    }

    /**
     * Returns the list of configured IPS objects.
     * @return array Data structure of the list property.
     */
    protected function GetObjects()
    {
        // Get the configured objects
        /** @noinspection PhpUndefinedMethodInspection */
        $text = $this->ReadPropertyString(self::PROPERTY_OBJECTS);

        // Decode the objects
        $objects = json_decode($text, true);

        // Assume empty array if the decode failed
        if (is_null($objects)) {
            $objects = array();
        }

        // Return the object list
        return $objects;
    }

    /**
     * Returns a list of IPS object IDs configured in the object list on the instance's configuration page.
     * @return array IPS object IDs of the objects.
     */
    public function GetObjectIds()
    {
        // Get the objects
        $objects = $this->GetObjects();

        // Extract the object IDs
        $objectIds = array();
        foreach ($objects as $entry) {
            $objectId = @$entry['objectId'];
            if (! is_null($objectId)) {
                $objectIds[] = $objectId;
            }
        }

        // Return the object ids
        return $objectIds;
    }

    /**
     * Returns a list of values used to populate the object list on the instance's configuration page.
     * @return array Values to be display in the list.
     */
    protected function GetObjectListValues()
    {
        // Get the object ids
        $objectIds = $this->GetObjectIds();

        // Get the interface name
        $interfaceName = $this->GetDriverInterfaceNames();

        // Loop through all mappings and generate the values array
        $colorOK = Color::Create(Color::IPS_OK);
        $colorError = Color::Create(Color::IPS_ERROR);
        $values = array();
        foreach ($objectIds as $objectId) {
            $rowColor = $colorOK;
            // Check if a driver can be found
            if ($objectId == 0) {
                $location = '';
                $status = Translator::Get('patami.patamiobjectgroup.form.objects.object_not_set');
                $rowColor = $colorError;
            } else {
                // Listen for instance reloads
                $this->ListenForInstanceReloads($objectId);
                // Get the object location
                $location = IPS::GetLocation($objectId);
                try {
                    // Detect the driver
                    /** @var Driver $driver */
                    $driver = Drivers::AutoDetect($objectId, $interfaceName);
                    $status = Translator::Format('patami.patamiobjectgroup.form.objects.driver_name', $driver->GetName());
                } catch (InvalidObjectException $e) {
                    // No driver found
                    $status = Translator::Get('patami.patamiobjectgroup.form.objects.driver_not_found');
                    $rowColor = $colorError;
                }
            }
            // Add an entry to the array
            $values[] = array(
                'rowColor' => $rowColor->GetHex(),
                'objectLocation' => $location,
                'objectId' => $objectId,
                'status' => $status
            );
        }

        // Return the values
        return $values;
    }

    /**
     * Returns the list of cached objects and drivers.
     * @return array List of objects and drivers.
     */
    protected function GetCachedObjects()
    {
        // Load the objects and drivers
        /** @noinspection PhpUndefinedMethodInspection */
        $data = $this->GetBuffer(self::OBJECTS_BUFFER_NAME);
        $objects = @json_decode($data, true);
        if (is_null($objects)) {
            $objects = array();
        }

        // Return the objects and drivers
        return $objects;
    }

    /**
     * Updates the configured list of IPS objects.
     * @param array $objects Data structure for the list property.
     */
    protected function SetObjects(array $objects)
    {
        // Remember the objects
        $id = $this->GetId();
        IPS::SetProperty($id, self::PROPERTY_OBJECTS, json_encode($objects));

        // Apply the changes
        IPS::ApplyChanges($id);
    }

    /**
     * Adds an IPS object ID to the list of configured objects.
     * @param int $id IPS object ID of the object to be added.
     */
    public function AddObject($id)
    {
        // Lock the property
        $semaphore = new Semaphore(sprintf('%d_objects', $this->GetId()));
        $semaphore->Enter();

        try {
            // Get the objects
            $objects = $this->GetObjects();

            // Search for the object ID
            foreach ($objects as $entry) {
                $objectId = @$entry['objectId'];
                // Skip forward to the finally block if the object ID already exists
                if ($objectId == $id) {
                    return;
                }
            }

            // Add a new entry
            $objects[]['objectId'] = $id;

            // Save the objects
            $this->SetObjects($objects);
        } finally {
            // Unlock the property
            $semaphore->Leave();
        }
    }

    /**
     * Removes an IPS object ID from the list of configured objects.
     * @param int $id IPS object ID of the object to be removed.
     */
    public function DeleteObject($id)
    {
        // Lock the property
        $semaphore = new Semaphore(sprintf('%d_objects', $this->GetId()));
        $semaphore->Enter();

        try {
            // Get the objects
            $objects = $this->GetObjects();

            // Remove all objects with the ID
            $newObjects = array();
            foreach ($objects as $entry) {
                $objectId = @$entry['objectId'];
                // Skip the entry if the object ID matches
                if ($objectId == $id) {
                    continue;
                }
                // Add the entry to the new list
                $newObjects[] = $entry;
            }

            // Save the objects
            $this->SetObjects($newObjects);
        } finally {
            // Unlock the property
            $semaphore->Leave();
        }
    }

    /**
     * Returns the status mode of the instance.
     * @return int Status mode of the instance.
     */
    public function GetStatusMode()
    {
        // Return the status mode
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_STATUS_MODE);
    }

    /**
     * Returns the minimum number of objects that need to be true for the result to be true.
     * @return int Minimum number of objects.
     */
    public function GetStatusMinObjectCount()
    {
        // Return the minimum number of objects
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_STATUS_MIN_OBJECT_COUNT);
    }

    /**
     * Checks if the status value should only be updated on changes of the monitored variables.
     * @return bool True if the status value is inverted.
     */
    public function IsStatusUpdatedOnChangeOnly()
    {
        // Check and return if the status is inverted
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyBoolean(self::PROPERTY_STATUS_CHANGED_ONLY);
    }

    /**
     * Checks if the status value should be inverted.
     * @return bool True if the status value is inverted.
     */
    public function IsStatusInverted()
    {
        // Check and return if the status is inverted
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyBoolean(self::PROPERTY_STATUS_INVERTED);
    }

    /**
     * Returns the list of configured value rules.
     * @return array Data structure of the list property.
     */
    protected function GetValueRules()
    {
        // Get the configured scenes
        /** @noinspection PhpUndefinedMethodInspection */
        $text = $this->ReadPropertyString(self::PROPERTY_VALUE_RULES);

        // Decode the value rules
        $valueRules = json_decode($text, true);

        // Assume empty array if the decode failed
        if (is_null($valueRules)) {
            $valueRules = array();
        }

        // Return the value rule list
        return $valueRules;
    }

    /**
     * Returns a list of values used to populate the value rules list on the instance's configuration page.
     * @return array Values to be display in the list.
     */
    protected function GetValueRuleValues()
    {
        // Get the duplicate value rule names
        $duplicateRuleNames = ArrayHelper::GetDuplicateEntries($this->GetValueRuleNames());

        // Get the value rules
        $valueRules = $this->GetValueRules();

        // Loop through all rules and generate the values array
        $colorOK = Color::Create(Color::IPS_OK);
        $colorError = Color::Create(Color::IPS_ERROR);
        $values = array();
        foreach ($valueRules as $valueRule) {
            $rowColor = $colorOK;
            // Mark the rule as invalid if the name is duplicate
            if (in_array($valueRule['ruleName'], $duplicateRuleNames)) {
                $rowColor = $colorError;
            }
            // Add an entry to the array
            $values[] = array(
                'rowColor' => $rowColor->GetHex()
            );
        }

        // Return the values
        return $values;
    }

    /**
     * Returns a list of configured value rule names.
     * @return array List of scene names.
     */
    public function GetValueRuleNames()
    {
        // Get the value rules
        $valueRules = $this->GetValueRules();

        // Loop through the value rules and generate the name list
        $names = array();
        foreach ($valueRules as $valueRule) {
            $names[] = $valueRule['ruleName'];
        }

        // Sort the list
        sort($names);

        // Return the list
        return $names;
    }

    /**
     * Returns the IPS object ident of the value rule value status variable.
     * @param string $name Name of the value rule.
     * @return string IPS object ident of the status variable.
     */
    public function GetValueRuleValueIdent($name)
    {
        return sprintf(self::VAR_VALUE_RULE_VALUE_FORMAT, sha1($name));
    }

    /**
     * Returns the IPS object ident of the value rule inverted value status variable.
     * @param string $name Name of the value rule.
     * @return string IPS object ident of the status variable.
     */
    public function GetValueRuleInvertedValueIdent($name)
    {
        return sprintf(self::VAR_VALUE_RULE_INVERTED_VALUE_FORMAT, sha1($name));
    }

    /**
     * Returns the IPS object ident of the value rule comparison value 1 status variable.
     * @param string $name Name of the value rule.
     * @return string IPS object ident of the status variable.
     */
    public function GetValueRuleComparisonValue1Ident($name)
    {
        return sprintf(self::VAR_VALUE_RULE_COMPARISON_VALUE_1_FORMAT, sha1($name));
    }

    /**
     * Returns the IPS object ident of the value rule comparison value 2 status variable.
     * @param string $name Name of the value rule.
     * @return string IPS object ident of the status variable.
     */
    public function GetValueRuleComparisonValue2Ident($name)
    {
        return sprintf(self::VAR_VALUE_RULE_COMPARISON_VALUE_2_FORMAT, sha1($name));
    }

    /**
     * Removes obsolete value rule status variables.
     * @param array $requiredIdents IPS variable idents of the remaining status variables.
     */
    protected function RemoveObsoleteValueRuleStatusVariables(array $requiredIdents = array())
    {
        // Get the instance
        $instance = $this->GetInstance();

        // Get the child objects
        $children = $instance->GetChildren();

        // Loop through all objects and remove the obsolete value rule variables
        /** @var Variable $child */
        foreach ($children as $child) {
            // Skip the object if it is not a variable
            if (!$child instanceof Variable) {
                continue;
            }
            // Get the ident
            $ident = $child->GetIdent();
            // Skip the variable if it is not a value rule variable
            if (! preg_match(self::VAR_VALUE_RULE_REGEX, $ident)) {
                continue;
            }
            // Skip the variable if it is still required
            if (in_array($ident, $requiredIdents)) {
                continue;
            }
            // Remove the variable
            /** @noinspection PhpUndefinedMethodInspection */
            $this->UnregisterVariable($ident);
        }
    }

    /**
     * Returns the list of configured scenes.
     * @return array Data structure of the list property.
     */
    protected function GetScenes()
    {
        // Get the configured scenes
        /** @noinspection PhpUndefinedMethodInspection */
        $text = $this->ReadPropertyString(self::PROPERTY_SCENES);

        // Decode the scenes
        $scenes = json_decode($text, true);

        // Assume empty array if the decode failed
        if (is_null($scenes)) {
            $scenes = array();
        }

        // Return the scene list
        return $scenes;
    }

    /**
     * Returns a list of values used to populate the scenes list on the instance's configuration page.
     * @return array Values to be display in the list.
     */
    protected function GetSceneValues()
    {
        // Get the duplicate scene names
        $duplicateSceneNames = ArrayHelper::GetDuplicateEntries($this->GetSceneNames());

        // Get the scenes
        $scenes = $this->GetScenes();

        // Loop through all scenes and generate the values array
        $colorOK = Color::Create(Color::IPS_OK);
        $colorError = Color::Create(Color::IPS_ERROR);
        $values = array();
        foreach ($scenes as $scene) {
            $rowColor = $colorOK;
            // Mark the scene as invalid if the name is duplicate
            if (in_array($scene['sceneName'], $duplicateSceneNames)) {
                $rowColor = $colorError;
            }
            // Add an entry to the array
            $values[] = array(
                'rowColor' => $rowColor->GetHex()
            );
        }

        // Return the values
        return $values;
    }

    /**
     * Returns a list of configured scene names.
     * @return array List of scene names.
     */
    public function GetSceneNames()
    {
        // Get the scenes
        $scenes = $this->GetScenes();

        // Loop through the scenes and generate the name list
        $names = array();
        foreach ($scenes as $scene) {
            $names[] = $scene['sceneName'];
        }

        // Sort the list
        sort($names);

        // Return the list
        return $names;
    }

    /**
     * Returns a data structure to be used for a scene name dropdown.
     * @return array Scene name drop down options.
     */
    protected function GetSceneNameDropDownOptions()
    {
        // Get the scene names
        $sceneNames = $this->GetSceneNames();

        // Loop through the names and generate the dropdown option list
        $options = array();
        foreach ($sceneNames as $sceneName) {
            $options[] = array(
                'label' => $sceneName,
                'value' => $sceneName
            );
        }

        // Return the dropdown options
        return $options;
    }

    /**
     * Updates the configured list of scenes.
     * @param array $scenes Data structure for the list property.
     */
    protected function SetScenes(array $scenes)
    {
        // Remember the scenes
        $id = $this->GetId();
        IPS::SetProperty($id, self::PROPERTY_SCENES, json_encode($scenes));

        // Apply the changes
        IPS::ApplyChanges($id);
    }

    /**
     * Returns the states of the configured scenes.
     * @return array List of scene states.
     */
    protected function GetSceneStates()
    {
        // Get the scene states
        /** @noinspection PhpUndefinedMethodInspection */
        $text = $this->ReadPropertyString(self::PROPERTY_SCENE_STATES);

        // Decode the scenes
        $sceneStates = json_decode($text, true);

        // Assume empty array if the decode failed
        if (is_null($sceneStates)) {
            $sceneStates = array();
        }

        // Return the scene states
        return $sceneStates;
    }

    /**
     * Returns the state of a scene.
     * @param string $name Name of the scene.
     * @return array State of the objects.
     */
    public function GetSceneState($name)
    {
        // Get the scene states
        $scenes = $this->GetSceneStates();

        // Get the scene state
        $state = @$scenes[$name];

        // Assume empty array if the scene was not found
        if (is_null($state)) {
            $state = array();
        }

        // Return the result
        return $state;
    }

    /**
     * Updates the states of the scenes.
     * @param array $sceneStates List of scene states.
     */
    protected function SetSceneStates(array $sceneStates)
    {
        // Remember the scene states
        $id = $this->GetId();
        IPS::SetProperty($id, self::PROPERTY_SCENE_STATES, json_encode($sceneStates));

        // Apply the changes
        IPS::ApplyChanges($id);
    }

    /**
     * Updates the state of a scene.
     * @param string $name Name of the scene.
     * @param array $state Scene state. Can be array().
     */
    protected function SetSceneState($name, array $state)
    {
        // Get the scene states
        $sceneStates = $this->GetSceneStates();

        // Set the state
        $sceneStates[$name] = $state;

        // Save the scene states
        $this->SetSceneStates($sceneStates);
    }

    /**
     * Removes the obsolete scenes and objects from the scene state.
     */
    public function CleanupSceneStates()
    {
        // Lock the property
        $semaphore = new Semaphore(sprintf('%d_scenes', $this->GetId()));
        $semaphore->Enter();

        try {
            // Get the scenes and states
            $scenesNames = $this->GetSceneNames();
            $sceneStates = $this->GetSceneStates();

            // Get the obsolete scenes
            $obsoleteSceneNames = array_diff(array_keys($sceneStates), $scenesNames);

            // Remove the obsolete scene states
            foreach ($obsoleteSceneNames as $sceneName) {
                $this->Debug('Cleanup', sprintf('Removing obsolete scene state %s', $sceneName));
                unset($sceneStates[$sceneName]);
            }

            // Get the objects
            $objectIds = $this->GetObjectIds();

            // Remove the obsolete objects
            foreach ($sceneStates as $sceneName => &$states) {
                foreach ($states as $objectId => $state) {
                    if (! in_array($objectId, $objectIds)) {
                        $this->Debug('Cleanup', sprintf('Removing obsolete object %d from scene %s', $objectId, $sceneName));
                        unset($states[$objectId]);
                    }
                }
            }

            // Save the scene states
            $this->SetSceneStates($sceneStates);

        } finally {
            // Unlock the property
            $semaphore->Leave();
        }
    }

    /**
     * Removes the obsolete scenes and objects from the scene state from the configuration form.
     */
    public function CleanupSceneStatesAction()
    {
        // Remove the obsolete scenes and objects
        $this->CleanupSceneStates();

        // Display the result
        echo Translator::Get('patami.patamiobjectgroup.form.actions.scene.cleanup_success');
    }

    /**
     * Creates links for all objects below the instance.
     */
    public function CreateLinks()
    {
        // Get the instance
        $instance = $this->GetInstance();

        // Get the object IDs
        $objectIds = $this->GetObjectIds();

        // Loop through the objects and create the links
        $index = 500;
        foreach ($objectIds as $objectId) {
            $object = Objects::GetByID($objectId);
            $link = new Link();
            $link->SetParent($instance)->SetName($object->GetName())->SetTarget($object)->SetPosition($index++);
        }
    }

    /**
     * Creates links for all objects below the instance from the configuration form.
     */
    public function CreateLinksAction()
    {
        // Create links
        $this->CreateLinks();

        // Display the result
        echo Translator::Get('patami.patamiobjectgroup.form.actions.scene.create_links_success');
    }

    /**
     * Adds a scene to the list of configured scenes.
     * If the scene already exists, the state of the scene is updated.
     * @param string $name Name of the scene.
     * @param array $state State of the objects. Can be array().
     */
    public function AddScene($name, array $state)
    {
        // Lock the property
        $semaphore = new Semaphore(sprintf('%d_scenes', $this->GetId()));
        $semaphore->Enter();

        try {
            // Get the scenes
            $scenes = $this->GetScenes();

            // Search for the scene name
            foreach ($scenes as &$entry) {
                $sceneName = @$entry['sceneName'];
                // Skip to the next entry if the scene name does not match
                if ($sceneName != $name) {
                    continue;
                }
                // The scene already exists
                $this->Debug($name, 'Updating scene');
                $this->Debug($name, serialize($state));
                // Update the state
                $this->SetSceneState($name, $state);
                // Skip forward to the finally block
                return;
            }

            // The scene doesn't exist yet
            $this->Debug($name, 'Adding scene');
            $this->Debug($name, serialize($state));

            // Add a new entry
            $scenes[]['sceneName'] = $name;
            $this->SetScenes($scenes);

            // Save the state
            $this->SetSceneState($name, $state);
        } finally {
            // Unlock the property
            $semaphore->Leave();
        }
    }

    /**
     * Deletes a scene from the list of configured scenes.
     * @param string $name Name of the scene.
     */
    public function DeleteScene($name)
    {
        // Lock the property
        $semaphore = new Semaphore(sprintf('%d_scenes', $this->GetId()));
        $semaphore->Enter();

        try {
            // Get the scenes
            $scenes = $this->GetScenes();

            // Remove all scenes with the name
            $newScenes = array();
            foreach ($scenes as $entry) {
                $sceneName = @$entry['sceneName'];
                // Skip the entry if the name matches
                if ($sceneName == $name) {
                    continue;
                }
                // Add the entry to the new list
                $newScenes[] = $entry;
            }

            // Save the scenes
            $this->SetScenes($newScenes);
        } finally {
            // Unlock the property
            $semaphore->Leave();
        }
    }

    /**
     * Updates the saved state of a scene with the current states of the objects.
     * If a scene with that name does not exist yet, it is added.
     * @param string $name Scene name.
     */
    public function UpdateScene($name)
    {
        // Add or update the scene with the current state
        $this->AddScene($name, $this->GetState());
    }

    /**
     * Updates the saved state of a scene with the current states of the objects from the configuration form.
     * @param string $name Scene name.
     */
    public function UpdateSceneAction($name)
    {
        // Check if the scene name is empty
        if ($name == '') {
            echo Translator::Get('patami.patamiobjectgroup.form.actions.scene.update_no_scene');
            return;
        }

        // Update the scene
        $this->UpdateScene($name);

        // Display the result
        echo Translator::Get('patami.patamiobjectgroup.form.actions.scene.update_success');
    }

    /**
     * Applies a scene.
     * @param string $name Scene name.
     * @return bool True if the scene was successfully applied.
     */
    public function ApplyScene($name)
    {
        // Get the scene state
        $state = $this->GetSceneState($name);

        // Return false if the scene was not found
        if (is_null($state)) {
            $this->Debug($name, 'Scene not found');
            return false;
        }

        // Set the scene ID
        $sceneId = array_search($name, $this->GetSceneNames());
        if ($sceneId !== false) {
            try {
                /** @var Variable $variable */
                $variable = $this->GetInstance()->GetChildByIdent(self::VAR_SCENE_ID);
                $variable->Set($sceneId);
            } catch (ObjectNotFoundException $e) {
                // Ignore the error
            }
        }

        // Apply the scene and return the result
        $this->Debug($name, 'Applying scene');
        return $this->SetState($state);
    }

    /**
     * Applies a scene from the configuration form.
     * @param string $name Scene name.
     */
    public function ApplySceneAction($name)
    {
        // Check if the scene name is empty
        if ($name == '') {
            echo Translator::Get('patami.patamiobjectgroup.form.actions.scene.apply_no_scene');
            return;
        }

        // Apply the scene and get the result
        $result = $this->ApplyScene($name);

        // Display the result
        if ($result === true) {
            return;
        } else if ($result === false) {
            echo Translator::Get('patami.patamiobjectgroup.form.actions.scene.apply_failed');
        } else {
            echo Translator::Get('patami.patamiobjectgroup.form.actions.scene.apply_incomplete');
        }
    }

    /**
     * Checks if scene UI controls should be created.
     * @return bool True if scene UI controls should be created.
     */
    public function IsSceneUIEnabled()
    {
        // Check and return if scene UI controls should be created
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyBoolean(self::PROPERTY_SCENE_UI_ENABLED);
    }

    /**
     * Returns the IPS object ident of the scene UI apply scene status variable.
     * @param string $name Name of the scene.
     * @return string IPS object ident of the status variable.
     */
    public function GetSceneUIApplySceneIdent($name)
    {
        return sprintf(self::VAR_SCENE_UI_APPLY_SCENE_FORMAT, sha1($name));
    }

    /**
     * Returns the IPS object ident of the scene UI update scene status variable.
     * @param string $name Name of the scene.
     * @return string IPS object ident of the status variable.
     */
    public function GetSceneUIUpdateSceneIdent($name)
    {
        return sprintf(self::VAR_SCENE_UI_UPDATE_SCENE_FORMAT, sha1($name));
    }

    /**
     * Removes obsolete scene UI status variables.
     * @param array $requiredIdents IPS variable idents of the remaining status variables.
     */
    protected function RemoveObsoleteSceneUIStatusVariables(array $requiredIdents = array())
    {
        // Get the instance
        $instance = $this->GetInstance();

        // Get the child objects
        $children = $instance->GetChildren();

        // Loop through all objects and remove the obsolete value rule variables
        /** @var Variable $child */
        foreach ($children as $child) {
            // Skip the object if it is not a variable
            if (!$child instanceof Variable) {
                continue;
            }
            // Get the ident
            $ident = $child->GetIdent();
            // Skip the variable if it is not a value rule variable
            if (! preg_match(self::VAR_SCENE_UI_REGEX, $ident)) {
                continue;
            }
            // Skip the variable if it is still required
            if (in_array($ident, $requiredIdents)) {
                continue;
            }
            // Remove the variable
            /** @noinspection PhpUndefinedMethodInspection */
            $this->UnregisterVariable($ident);
        }
    }

    /**
     * Checks if switching is enabled.
     * @return bool True if switching is enabled.
     */
    public function IsSceneSwitchingEnabled()
    {
        // Check and return if switching is enabled
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyBoolean(self::PROPERTY_SCENE_SWITCH_ENABLED);
    }

    /**
     * Returns the switch off scene name.
     * @return string Name of the switch off scene.
     */
    public function GetSceneSwitchOffScene()
    {
        // Get and return the switch off scene name
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyString(self::PROPERTY_SCENE_SWITCH_OFF_SCENE);
    }

    /**
     * Returns the switch on scene name.
     * @return string Name of the switch on scene.
     */
    public function GetSceneSwitchOnScene()
    {
        // Get and return the switch on scene name
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyString(self::PROPERTY_SCENE_SWITCH_ON_SCENE);
    }

    /**
     * Checks if the alternate switch on scene is enabled.
     * @return bool True if the alternate switch on scene is enabled.
     */
    public function IsAlternateSwitchOnSceneEnabled()
    {
        // Check and return if the alternate switch on scene is enabled
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyBoolean(self::PROPERTY_SCENE_SWITCH_ON_ALT_ENABLED);
    }

    /**
     * Returns the IPS object ID of the variable which is used to decide whether the alternate switch on scene should be used.
     * @return int Alternate switch on scene variable ID.
     */
    public function GetAlternateSwitchOnSceneVariableId()
    {
        // Get and return the variable ID
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyInteger(self::PROPERTY_SCENE_SWITCH_ON_ALT_VARIABLE_ID);
    }

    /**
     * Returns the alternate switch on scene name.
     * @return string Name of the alternate switch on scene.
     */
    public function GetAlternateSwitchOnScene()
    {
        // Get and return the alternate switch on scene name
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->ReadPropertyString(self::PROPERTY_SCENE_SWITCH_ON_ALT_SCENE);
    }

    /**
     * Switches on.
     * @return bool True if switching was successful, false if not.
     */
    public function SetSwitchOn()
    {
        // Switch on
        return $this->SetSwitch(true);
    }

    /**
     * Switches off.
     * @return bool True if switching was successful, false if not.
     */
    public function SetSwitchOff()
    {
        // Switch off
        return $this->SetSwitch(false);
    }

    /**
     * Switches on or off by applying the configured scene.
     * @param bool $value True to switch on, false to switch off.
     * @return bool True if switching was successful, false if not.
     */
    public function SetSwitch($value)
    {
        // Return if the instance is not valid
        if (! $this->IsValid()) {
            return false;
        }

        // Return if switching is disabled
        if (! $this->IsSceneSwitchingEnabled()) {
            return false;
        }

        // Determine the scene name
        if ($value) {
            // Switch on
            $tag = 'Switch On';
            $sceneName = $this->GetSceneSwitchOnScene();
            if ($this->IsAlternateSwitchOnSceneEnabled()) {
                $variableId = $this->GetAlternateSwitchOnSceneVariableId();
                $variable = new BooleanVariable($variableId);
                $useAlternateSceneName = $variable->Get();
                if ($useAlternateSceneName) {
                    $sceneName = $this->GetAlternateSwitchOnScene();
                }
            }
        } else {
            // Switch off
            $tag = 'Switch Off';
            $sceneName = $this->GetSceneSwitchOffScene();
        }
        $this->Debug($tag, sprintf('Scene name: %s', $sceneName));

        // Apply the scene and return the result
        return $this->ApplyScene($sceneName);
    }

    /**
     * Processes variable updates.
     * @param string $ident Ident of the status variable.
     * @param mixed $value New value of the status variable.
     */
    public function RequestAction($ident, $value)
    {
        // Get the instance
        $instance = $this->GetInstance();

        if ($ident == self::VAR_SCENE_ID) {
            // SceneID variable
            // Get the scene name
            $sceneNames = $this->GetSceneNames();
            $sceneName = @$sceneNames[$value];
            if (is_null($sceneName)) {
                // Log an error if no matching scene could be found
                $this->Debug('SceneID Action', sprintf('Unmapped value %d', $value));
            } else {
                // Apply the scene
                $this->ApplyScene($sceneName);
            }
        } elseif (preg_match(self::VAR_SCENE_UI_REGEX, $ident, $tokens)) {
            // Scene action variable
            // Get the scene name
            /** @var Variable $variable */
            $variable = $instance->GetChildByIdent($ident);
            $sceneName = $variable->GetName();
            // Get the action
            $action = $tokens[1];
            if ($action == 'Apply') {
                $this->ApplyScene($sceneName);
            } elseif ($action == 'Update') {
                $this->UpdateScene($sceneName);
            }
        } elseif ($ident == self::VAR_VALUE) {
            // Value variable
            // Switch the instance
            $this->SetSwitch($value);
        } elseif (preg_match(self::VAR_VALUE_RULE_REGEX, $ident)) {
            // Value rule variable
            // Set the variable
            /** @var Variable $variable */
            $variable = $instance->GetChildByIdent($ident);
            $variable->Set($value);
            // The status is automatially updated because the variables are monitored by the instance
        } else {
            // Unknown variable
            $this->Debug('Variable Action', sprintf('Unexpected action for variable %s (value %s)', $ident, $value));
        }
    }

    protected function OnMessage($timestamp, $senderId, $messageId, $data)
    {
        switch ($messageId) {
            case OM_UNREGISTER:
                // An object from the list was deleted
                $this->OnObjectDeleted($senderId);
                break;
            case VM_UPDATE:
                // A monitored variable was updated
                $newValue = @$data[0];
                $oldValue = @$data[2];
                if (($newValue != $oldValue) || ! $this->IsStatusUpdatedOnChangeOnly()) {
                    // Only process the message if the variable actually changed
                    $this->OnVariableChanged($senderId, $newValue, $oldValue);
                }
                break;
            default:
                // Unexpected message received
                $this->Debug($senderId, sprintf('Unexpected message %d', $messageId));
        }
    }

    /**
     * Event handler called when objects are deleted.
     * @param int $objectId IPS object ID of the object that that triggered the message.
     */
    protected function OnObjectDeleted($objectId)
    {
        // Log the event
        $this->Debug($objectId, 'Object deleted');

        // Remove the object from the list
        $this->DeleteObject($objectId);
    }

    /**
     * Event handler called when variables are changed.
     * @param int $objectId IPS object ID of the object that that triggered the message.
     * @param mixed $newValue New variable value.
     * @param mixed $oldValue Old variable value.
     */
    protected function OnVariableChanged($objectId, $newValue, $oldValue)
    {
        // Log the event
        $this->Debug($objectId, sprintf('Monitored variable changed (%s => %s)', serialize($oldValue), serialize($newValue)));

        // Update the status variables
        $this->UpdateStatus();
    }

    /**
     * Updates the status variables.
     */
    protected function UpdateStatus()
    {
        // Get the mode
        $mode = $this->GetMode();

        // Call the specialized update method
        if ($mode == self::MODE_STATUS || $mode == self::MODE_SCENE) {
            $this->UpdateStatusModeStatus();
        } else {
            $this->UpdateValueModeStatus();
        }
    }

    /**
     * Updates the status variables when the instance is in "status" or "scene" mode.
     */
    protected function UpdateStatusModeStatus()
    {
        // Load the objects and drivers
        $objects = $this->GetCachedObjects();
        $objectCount = count($objects);

        // Loop through the objects
        $objectTrueCount = 0;
        foreach ($objects as $objectId => $driverClassName) {
            try {
                // Load the driver
                /** @var GetSwitchInterface $driver */
                $driver = new $driverClassName($objectId);
                // Get the switch value
                if ($driver->GetSwitch()) {
                    $objectTrueCount++;
                }
            } catch (InvalidObjectException $e) {
                // There was an error loading the driver
                $this->Debug('Status Update', 'Unable to load driver for %d', $objectId);
            }
        }

        // Determine the result
        $result = false;
        $statusMode = $this->GetStatusMode();
        switch ($statusMode) {
            case self::STATUS_MODE_AND:
                // All objects must be true (boolean AND)
                $result = ($objectCount == $objectTrueCount);
                break;
            case self::STATUS_MODE_OR:
                // At least one object must be true (boolean OR)
                $result = ($objectTrueCount > 0);
                break;
            case self::STATUS_MODE_COUNT:
                // The configured number of objects must be true
                $result = ($objectTrueCount >= $this->GetStatusMinObjectCount());
                break;
        }

        // Check if the status should be inverted
        if ($this->IsStatusInverted()) {
            $result = ! $result;
        }

        // Set the result to null when no objects are configured
        if ($objectCount == 0) {
            $result = null;
        }

        // Get the instance object
        $instance = $this->GetInstance();

        // Update the true object count
        /** @var Variable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_TRUE_OBJECT_COUNT);
        $variable->Set($objectTrueCount);

        // Update the false object count
        $variable = $instance->GetChildByIdent(self::VAR_FALSE_OBJECT_COUNT);
        $variable->Set($objectCount - $objectTrueCount);

        // Update the value
        $variable = $instance->GetChildByIdent(self::VAR_VALUE);
        if ($variable->Get() != $result) {
            $this->Debug('Status Update', sprintf('New value: %s', $result? 'True': 'False'));
        }
        $variable->Set($result);

        // Update the inverted value
        $variable = $instance->GetChildByIdent(self::VAR_INVERTED_VALUE);
        $variable->Set(! $result);
    }

    /**
     * Updates the status variables when the instance is in "value" mode.
     */
    protected function UpdateValueModeStatus()
    {
        // Load the objects and drivers
        $objects = $this->GetCachedObjects();

        // Loop through the objects and determine the status values
        $objectCount = 0;
        $sum = null;
        $minimum = null;
        $maximum = null;
        foreach ($objects as $objectId => $driverClassName) {
            try {
                // Load the driver
                /** @var GetNumberInterface $driver */
                $driver = new $driverClassName($objectId);
                // Get the value
                $value = $driver->GetNumber();
                // Update the status values
                $objectCount++;
                if (is_null($sum)) {
                    $sum = $value;
                } else {
                    $sum += $value;
                }
                if (is_null($minimum) || $value < $minimum) {
                    $minimum = $value;
                }
                if (is_null($maximum) || $value > $maximum) {
                    $maximum = $value;
                }
            } catch (InvalidObjectException $e) {
                // There was an error loading the driver
                $this->Debug('Status Update', 'Unable to load driver for %d', $objectId);
            }
        }
        if ($objectCount == 0) {
            $average = null;
        } else {
            $average = $sum / $objectCount;
        }
        $difference = $maximum - $minimum;

        // Get the instance object
        $instance = $this->GetInstance();

        // Update the sum variable
        /** @var Variable $variable */
        $variable = $instance->GetChildByIdent(self::VAR_VALUE_SUM);
        if ($variable->Get() != $sum) {
            $this->Debug('Status Update', sprintf('New sum value: %f', $sum));
        }
        $variable->Set($sum);

        // Update the average variable
        $variable = $instance->GetChildByIdent(self::VAR_VALUE_AVERAGE);
        if ($variable->Get() != $average) {
            $this->Debug('Status Update', sprintf('New average value: %f', $average));
        }
        $variable->Set($average);

        // Update the minimum variable
        $variable = $instance->GetChildByIdent(self::VAR_VALUE_MINIMUM);
        if ($variable->Get() != $minimum) {
            $this->Debug('Status Update', sprintf('New minimum value: %f', $minimum));
        }
        $variable->Set($minimum);

        // Update the maximum variable
        $variable = $instance->GetChildByIdent(self::VAR_VALUE_MAXIMUM);
        if ($variable->Get() != $maximum) {
            $this->Debug('Status Update', sprintf('New maximum value: %f', $maximum));
        }
        $variable->Set($maximum);

        // Update the difference variable
        $variable = $instance->GetChildByIdent(self::VAR_VALUE_DIFFERENCE);
        if ($variable->Get() != $difference) {
            $this->Debug('Status Update', sprintf('New difference value: %f', $difference));
        }
        $variable->Set($difference);

        // Evaluate value rules and update the value rule status variables
        $rules = $this->GetValueRules();
        foreach ($rules as $rule) {
            $name = $rule['ruleName'];
            $valueType = $rule['value'];
            $operator = $rule['comparisonOperator'];
            // Determine the reference value
            $value = null;
            switch ($valueType) {
                case self::VALUE_RULE_VALUE_SUM:
                    $value = $sum;
                    break;
                case self::VALUE_RULE_VALUE_AVERAGE:
                    $value = $average;
                    break;
                case self::VALUE_RULE_VALUE_MINIMUM:
                    $value = $minimum;
                    break;
                case self::VALUE_RULE_VALUE_MAXIMUM:
                    $value = $maximum;
                    break;
                case self::VALUE_RULE_VALUE_DIFFERENCE:
                    $value = $difference;
                    break;
                default:
                    // Something went wrong, skip the rule
                    continue;
            }
            // Get the comparison values
            $ident = $this->GetValueRuleComparisonValue1Ident($name);
            $variable = $instance->GetChildByIdent($ident);
            $comparisonValue1 = $variable->Get();
            $comparisonValue2 = null;
            if (Number::IsInRange($operator, self::VALUE_RULE_COMPARISON_OPERATOR_BETWEEN, self::VALUE_RULE_COMPARISON_OPERATOR_NOT_BETWEEN_OR_EQUAL)) {
                $ident = $this->GetValueRuleComparisonValue2Ident($name);
                $variable = $instance->GetChildByIdent($ident);
                $comparisonValue2 = $variable->Get();
            }
            // Calculate the result
            switch ($operator) {
                case self::VALUE_RULE_COMPARISON_OPERATOR_EQUAL:
                    $result = ($value == $comparisonValue1);
                    break;
                case self::VALUE_RULE_COMPARISON_OPERATOR_NOT_EQUAL:
                    $result = ($value != $comparisonValue1);
                    break;
                case self::VALUE_RULE_COMPARISON_OPERATOR_LARGER:
                    $result = ($value > $comparisonValue1);
                    break;
                case self::VALUE_RULE_COMPARISON_OPERATOR_LARGER_OR_EQUAL:
                    $result = ($value >= $comparisonValue1);
                    break;
                case self::VALUE_RULE_COMPARISON_OPERATOR_SMALLER:
                    $result = ($value < $comparisonValue1);
                    break;
                case self::VALUE_RULE_COMPARISON_OPERATOR_SMALLER_OR_EQUAL:
                    $result = ($value <= $comparisonValue1);
                    break;
                case self::VALUE_RULE_COMPARISON_OPERATOR_BETWEEN:
                    $result = (($value > $comparisonValue1) && ($value < $comparisonValue2));
                    break;
                case self::VALUE_RULE_COMPARISON_OPERATOR_NOT_BETWEEN:
                    $result = ! (($value > $comparisonValue1) && ($value < $comparisonValue2));
                    break;
                case self::VALUE_RULE_COMPARISON_OPERATOR_BETWEEN_OR_EQUAL:
                    $result = (($value >= $comparisonValue1) && ($value <= $comparisonValue2));
                    break;
                case self::VALUE_RULE_COMPARISON_OPERATOR_NOT_BETWEEN_OR_EQUAL:
                    $result = ! (($value >= $comparisonValue1) && ($value <= $comparisonValue2));
                    break;
                default:
                    // Something went wrong, skip the rule
                    $result = false;
                    continue;
            }
            // Update the status values
            $ident = $this->GetValueRuleValueIdent($name);
            $variable = $instance->GetChildByIdent($ident);
            $variable->Set($result);
            $ident = $this->GetValueRuleInvertedValueIdent($name);
            $variable = $instance->GetChildByIdent($ident);
            $variable->Set(! $result);
        }
    }

    /**
     * Returns the state of all configured IPS objects.
     * This method can only be used in the "Scene" mode.
     * It returns false if the instance is invalid or if the mode is other than "scene".
     * @return array|false States of the objects or false if the state cannot be determined.
     */
    public function GetState()
    {
        // Return false if the instance is not valid
        if (! $this->IsValid()) {
            return false;
        }

        // Return false if the mode is incorrect
        if ($this->GetMode() != self::MODE_SCENE) {
            return false;
        }

        // Load the objects and drivers
        $objects = $this->GetCachedObjects();

        // Loop through the objects
        $state = array();
        foreach ($objects as $objectId => $driverClassName) {
            try {
                // Load the driver
                /** @var StateInterface $driver */
                $driver = new $driverClassName($objectId);
                // Get the state of the object
                $state[$objectId] = $driver->GetState();
            } catch (InvalidObjectException $e) {
                // There was an error loading the driver
                $this->Debug('Get State', 'Unable to load driver for %d', $objectId);
            }
        }

        // Return the state of the objects
        return $state;
    }

    /**
     * Sets the state of all configured IPS objects.
     * This method can only be used in the "Scene" mode.
     * It returns false if the instance is invalid or if the mode is other than "scene" or if setting the state of one
     * of the objects failed.
     * @param array $state States of the objects.
     * @return bool|int True if the state was successfully set for all objects, false if there was an error or the
     * number of failed objects.
     */
    public function SetState(array $state)
    {
        // Return false if the instance is not valid
        if (! $this->IsValid()) {
            return false;
        }

        // Return false if the mode is incorrect
        if ($this->GetMode() != self::MODE_SCENE) {
            return false;
        }

        // Load the objects and drivers
        $objects = $this->GetCachedObjects();

        // Loop through the objects
        $failed = 0;
        foreach ($objects as $objectId => $driverClassName) {
            // Skip the object if no state information was passed
            if (! isset($state[$objectId])) {
                $this->Debug('Set State', sprintf('No state information for %d', $objectId));
                $failed++;
                continue;
            }
            try {
                // Load the driver
                /** @var StateInterface $driver */
                $driver = new $driverClassName($objectId);
                // Set the state of the object
                $this->Debug($objectId, serialize($state[$objectId]));
                $driver->SetState($state[$objectId]);
            } catch (InvalidObjectException $e) {
                // There was an error loading the driver
                $this->Debug('Set State', sprintf('Unable to load driver for %d', $objectId));
                $failed++;
            } catch (StateException $e) {
                // There was an error setting the state
                $this->Debug('Set State', sprintf('Unable to set the state of %d', $objectId));
                $failed++;
            }
        }

        // Return the result
        if ($failed == 0) {
            return true;
        } elseif (count($objects) == $failed) {
            return false;
        } else {
            return $failed;
        }
    }

    protected function GetNewIssueUrl()
    {
        return 'https://bitbucket.org/patami/ipspatami/issues/new';
    }

    protected function GetLicenseUrl()
    {
        return 'https://docs.braintower.de/display/IPSPATAMI/Lizenz';
    }

    protected function GetDocumentationUrl()
    {
        return 'https://docs.braintower.de/display/IPSPATAMI/Patami+Object+Group+Modul';
    }

    protected function GetReleaseNotesUrl()
    {
        return 'https://docs.braintower.de/display/IPSPATAMI/Versionshinweise';
    }

}