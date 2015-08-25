<?php

namespace Eva\EvaPermission\Controllers;

use Eva\EvaEngine\Mvc\Controller\ControllerBase;

class ErrorController extends ControllerBase
{
    public function indexAction()
    {
        $login = new Login();
        $login->logout();
        return $this->response->redirect(eva_url('passport', '/login', [
            'next' => $this->currentUrl()
        ]));
        $content = <<<EOF
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Page Not Found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>

        * {
            line-height: 1.5;
            margin: 0;
        }

        html {
            color: #888;
            font-family: sans-serif;
            text-align: center;
        }

        body {
            left: 50%;
            margin: -43px 0 0 -150px;
            position: absolute;
            top: 50%;
            width: 300px;
        }

        h1 {
            color: #555;
            font-size: 2em;
            font-weight: 400;
        }

        p {
            line-height: 1.2;
        }

        @media only screen and (max-width: 270px) {

            body {
                margin: 10px auto;
                position: static;
                width: 95%;
            }

            h1 {
                font-size: 1.5em;
            }

        }

    </style>
</head>
<body>
    <h1>Permission Not Allowed</h1>
    <p>Sorry, you don t have permission to access this page, please try login.</p>
</body>
</html>
EOF;
        $this->response->setStatusCode(401, $this->recommendedReasonPhrases[401]);
        $this->response->setContent($content);
        return $this->response;
    }
}
