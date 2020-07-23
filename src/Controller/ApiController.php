<?php

namespace App\Controller;

use App\Entity\Region;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\RegionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/regions/api", name="api_add_region_api",methods={"GET"})
     */
    public function addRegionByApi(SerializerInterface $serializer)
    {
        $regionJson=file_get_contents("https://geo.api.gouv.fr/regions");
       /*  //1-Methode 1
        // Decode Json vers Tableau
        $regionTab=$serializer->decode($regionJson,"json");
        // Denormalisation de tableau vers objet ou tableau Objet
        $regionObject=$serializer->denormalize($regionTab, 'App\Entity\Region[]');
        dd($regionObject); */

        //2-Methode 2 : Deserialisation Json vers Objectou Tableau
        $regionObject = $serializer->deserialize($regionJson,'App\Entity\Region[]','json');
/*         dd($regionObject); */
            // Maitenant on créé des regions
$entityManager = $this->getDoctrine()->getManager();
        foreach($regionObject as $region){
            $entityManager->persist($region);

 }
 $entityManager->flush();
 

 return new JsonResponse("succes",Response::HTTP_CREATED,[],true);

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ApiController.php',
        ]);
    }

    /**
     * @Route("/api/regions", name="api_show_region",methods={"GET"})
     */
    public function showRegion(SerializerInterface $serializer, RegionRepository $repo)
    {
        $regionsObject=$repo->findAll();
        $regionsJson =$serializer->serialize($regionsObject,"json",
        [
            "groups"=>["region:read_all"]  // Pour eviter les references circulaires
        ]
        );
        return new JsonResponse($regionsJson,Response::HTTP_OK,[],true);
    }

        //Ajouter regions
    /**
     * @Route("/api/regions", name="api_add_region",methods={"POST"})
     */
    public function addRegion(SerializerInterface $serializer, Request $request, ValidatorInterface $validator)
    {
        // Recuperer le contenu du body de la requete
        $regionJson=$request->getContent();
        $region = $serializer->deserialize($request->getContent(), Region::class,'json');
        // Validation des données
        $errors = $validator->validate($region);
        if (count($errors) > 0) {
        $errorsString =$serializer->serialize($errors,"json");
        return new JsonResponse( $errorsString ,Response::HTTP_BAD_REQUEST,[],true);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($region);
        $entityManager->flush();
        return new JsonResponse("succes",Response::HTTP_CREATED,[],true);
       /*  dd($regionJson); */
    }
}
