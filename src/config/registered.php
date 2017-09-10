<?php

return [

    /**
     * Configure the invitation workflow and requirements
     *
     * - **required:** Whether an invitation is required for registration.
     * - **requestable:** Whether users can request an invitation be sent to them.
     *
     * Note: For open enrollment, set required to false; it does not matter if 
     * requestable is true or false at that point.
     * 
     */
    'invitations' => [
        'required' => true,
        'requestable' => false
    ],

    /**
     * Path to your terms of service page
     *
     * If text is present, a checkbox with a link to the URL will be visible on the
     * registration form. Further, it will need to be checked in order to complete
     * the registration process. Finally, you will be able to mark your TOS as updated
     * to notify and request users agree to the terms of service again.
     * 
     */
    'tos_url' => '',

    /**
     * Whether to use Registered's front-end capabilities
     *
     * The term "headless" is being misused slightly here.
     *
     * - **views:** Whether to load and publish Registered's views. If set to true, 
     *              routes will not be registered either.
     * - **routes:** Whether to register the routes for the package. If set to true,
     *               the views will be available to you, as well as the controllers;
     *               however, you will have to generate your own routes.
     */
    'headless' => [
        'views' => false,
        'routes' => false
    ]
];
