<?php

namespace App\Controller;

use App\Repository\ContentRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Request\ParamFetcherInterface;

/**
 * Class WorkController
 * @package App\Controller
 *
 * @Route("/api/v1",name="api_")
 */
class ContentController extends AbstractFOSRestController
{
    /**
     * @param ContentRepository $contentRepository
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/contents",name="contents")
     *
     * @QueryParam(map=true, name="toc_entries", requirements="\d+", description="List of toc entries")
     * @QueryParam(map=true, name="editions", requirements="\d+", description="List of editions")
     */
    public function getContents(ParamFetcherInterface $paramFetcher, ContentRepository $contentRepository)
    {
        $tocEntries = $paramFetcher->get('toc_entries');
        $editions = $paramFetcher->get('editions');

        $contents = $contentRepository->createQueryBuilder('c')
            ->andWhere("c.tocEntry IN(:tocEntryIds)")
            ->andWhere("c.edition IN(:editionIds)")
            ->setParameter('tocEntryIds', array_values($tocEntries))
            ->setParameter('editionIds', array_values($editions))
            ->setMaxResults(30)
            ->getQuery()->getResult();

        $view = $this->view($contents, 200);
        $view->getContext()->setGroups(['content_details']);

        $response = $this->handleView($view);

        $response->setPublic();
        $response->setMaxAge(3600);

        return $response;
    }
}