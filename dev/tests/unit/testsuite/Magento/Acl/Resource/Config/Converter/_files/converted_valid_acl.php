<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
return array(
    'config' => array(
        'acl' => array(
            'resources' => array(
                array(
                    'id' => 'Custom_Module::resource_one',
                    'title' => 'Resource One Title',
                    'sortOrder' => 10,
                    'disabled' => true,
                    'children' => array(),
                ),
                array(
                    'id' => 'Custom_Module::resource_two',
                    'title' => 'Resource Two Title',
                    'sortOrder' => 20,
                    'disabled' => false,
                    'children' => array(),
                ),
                array(
                    'id' => 'Custom_Module::parent_resource',
                    'title' => 'Parent Resource Title',
                    'sortOrder' => 50,
                    'disabled' => false,
                    'children' => array(
                        array(
                            'id' => 'Custom_Module::child_resource_one',
                            'title' => 'Resource Child Title',
                            'sortOrder' => 30,
                            'disabled' => false,
                            'children' => array(
                                array(
                                    'id' => 'Custom_Module::child_resource_two',
                                    'title' => 'Resource Child Level 2 Title',
                                    'sortOrder' => 10,
                                    'disabled' => false,
                                    'children' => array(),
                                ),
                            ),

                        ),
                    ),
                ),
            ),
        ),
    ),
);
