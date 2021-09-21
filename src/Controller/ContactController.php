<?php


namespace App\Controller;


use App\Repository\WorkRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Class WorkController
 * @package App\Controller
 *
 * @Route("/api/v1",name="api_")
 */
class ContactController extends AbstractFOSRestController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/contact", name="contact", methods={"POST"})
     */
    public function postContact(Request $request, MailerInterface $mailer)
    {
        //
        $contactName = $request->request->get('name');
        $contactEmail = $request->request->get('email');
        $message = $request->request->get('message');

        $email = (new Email())
            ->from('contact@stoicsource.com')
            ->to('patrick.menke@posteo.de')
            ->subject('StoicSource Contact Form Submission')
            ->text("From: $contactName ($contactEmail)" . PHP_EOL . PHP_EOL . $message);

        $mailer->send($email);

        return new Response();
    }
}