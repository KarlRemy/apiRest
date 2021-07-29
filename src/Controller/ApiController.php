<?php

namespace App\Controller;

use App\Entity\Sport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/sport", name="api_index", methods={"GET"})
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function index(SerializerInterface $serializer): Response
    {
        $sports = $this->getDoctrine()->getRepository(Sport::class)->findAll();
        $json = $serializer->serialize($sports, 'json');

        return new JsonResponse($json, 200, [], true);
    }

    /**
     * @Route("/api/sport/{id}", name="api_index_id", methods={"GET"})
     * @param SerializerInterface $serializer
     * @param $id
     * @return Response
     */
    public function sportId(SerializerInterface $serializer, $id): Response
    {
        $sport = $this->getDoctrine()->getRepository(Sport::class)->find($id);
        $json = $serializer->serialize($sport, 'json');

        return new JsonResponse($json, 200, [], true);
    }

    /**
     * @Route("/api/sport", name="api_index_create", methods={"POST"})
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function sportCreate(SerializerInterface $serializer, EntityManagerInterface $em, Request $request, ValidatorInterface $validator): Response
    {
        $recuJson = $request->getContent();

        try {
            $sport = $serializer->deserialize($recuJson, Sport::class, 'json');

            $errors = $validator->validate($sport);

            if(count($errors) > 0){
                return $this->json($errors, 400);
            }

            $em->persist($sport);
            $em->flush();

            return new JsonResponse($sport, 201, []);

        } catch (NotEncodableValueException $e) {
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
        }

    }

    /**
     * @Route("/api/sport/{id}", name="api_index_modif", methods={"PUT"})
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param $id
     * @return Response
     */
    public function sportModif(SerializerInterface $serializer, EntityManagerInterface $em, Request $request, ValidatorInterface $validator, $id): Response
    {
        $recuJson = $request->getContent();

        try {
            $json = $serializer->deserialize($recuJson, Sport::class, 'json');
            $errors = $validator->validate($json);

            if(count($errors) > 0){
                return $this->json($errors, 400);
            }

            $sport = $this->getDoctrine()->getRepository(Sport::class)->find($id);
            $sport->setLabel($json->getLabel());

            $em->persist($sport);
            $em->flush();

            return new JsonResponse($sport, 201, []);

        } catch (NotEncodableValueException $e) {
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @Route("/api/sport/{id}", name="api_index_delete", methods={"DELETE"})
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param $id
     * @return Response
     */
    public function sportDelete(SerializerInterface $serializer, EntityManagerInterface $em, $id): Response
    {
        $sport = $this->getDoctrine()->getRepository(Sport::class)->find($id);
        if(!empty($sport)) {
            $json = $serializer->serialize($sport, 'json');

            $em->remove($sport);
            $em->flush();

            return new JsonResponse($json, 200, [], true);
        } else {
            return $this->json([
                'status' => 400,
                'message' => 'empty sport'
            ], 400);
        }

    }
}
