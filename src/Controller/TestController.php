<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    #[Route('/test', name: 'test')]
    public function test(): Response
    {
        return new Response('Test route works!');
    }
}