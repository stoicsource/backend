<?php


namespace App\Controller;


use App\Repository\EditionRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/v1', name: 'api_')]
class EditionController extends AbstractFOSRestController
{
    #[Route('/edition/{id}', name: 'edition_details')]
    public function getEditionDetails($id, EditionRepository $editionRepository): Response
    {
        $edition = $editionRepository->find($id);
        $view = $this->view($edition, 200);
        $view->getContext()->setGroups(['edition_details']);

        $response = $this->handleView($view);

        $response->setPublic();
        $response->setMaxAge(3600);

        return $response;
    }
}