<?php

declare(strict_types=1);

namespace App\Infrastructure\UI\Web;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController
{
    /**
     * @Route("/basic-test", name="app_basic_test")
     */
    public function basicTest(): Response
    {
        return new Response(
            '<html lang="en"><body>The content has been loaded.</body></html>'
        );
    }
}
