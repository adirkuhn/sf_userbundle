<?php

namespace AdirKuhn\UserBundle\Controller;

use AdirKuhn\UserBundle\Entity\User;
use AdirKuhn\UserBundle\Form\UserType;
use Symfony\Bridge\Propel1\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class UserController extends Controller
{
    /**
     * List all users
     *
     * @author Adir Kuhn <adirkuhn@gmail.com>
     *
     * @return Response HTTP Response Json with all users
     */
    public function indexAction()
    {
        $entityManager = $this->getDoctrine()->getManager();

        //find all users
        $users = $entityManager->getRepository("UserBundle:User")->findAll();





        var_dump($users, $j);

        $response = new JsonResponse();
        $response->setData($users);

        return $response;
    }

    /**
     * add User and return the created resource.
     * 
     * @author Adir Kuhn <adirkuhn@gmail.com>
     *
     * @return Response HTTP Response
     */
    public function userAddAction(Request $request)
    {

        $data = $request->request->all();

        //new user
        $newUser = new User();

        //"submit" data to user form
        $form = $this->createForm(new UserType(), $newUser);
        $form->submit($data, false);

        //response and persist data
        $response = new JsonResponse();
        if($form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();

            //set encoded password
            $newUser->setPassword($this->encodePassword($newUser, $newUser->getPassword()));

            //save
            $entityManager->persist($newUser);
            $entityManager->flush();

            //response code
            $response->setStatusCode(JsonResponse::HTTP_CREATED);

            //set body
            $data['id'] = $newUser->getId();
            $response->setData($data);

        }
        else {
            $response->setStatusCode(JsonResponse::HTTP_BAD_REQUEST);
            $response->setData($form->getErrors());
        }

        return $response;
    }

    /**
     * @param $user
     * @param $plainTextPassword
     * @return mixed
     */
    private function encodePassword($user, $plainTextPassword) {
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);

        return $encoder->encodePassword($plainTextPassword, $user->getSalt());
    }

    /**
     * User delete by id
     *
     * @author Adir Kuhn <adirkuhn@gmail.com>
     *
     * @param $id int User's id
     *
     * @return Response HTTP Response
     */
    public function userDeleteAction($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $userToDelete = $entityManager->getRepository('UserBundle:User')->find($id);

        $response = new JsonResponse();
        if($userToDelete) {

            //delete user
            $entityManager->remove($userToDelete);
            $entityManager->flush();

            $response->setData('');
            $response->setStatusCode(JsonResponse::HTTP_NO_CONTENT);
        }
        else {

            $response->setData('Resource not found.');
            $response->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
        }

        return $response;
    }

    /**
     * Get user information
     *
     * @author Adir Kuhn <adirkuhn@gmail.com>
     *
     * @param $id int User's id
     *
     * @return Response JsonResponse User's info
     */
    public function userGetAction($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $userInfo = $entityManager->find('UserBundle:User', $id);

        $response = new JsonResponse();
        if ($userInfo) {

            $normalizer = new GetSetMethodNormalizer();
            $normalizer->setIgnoredAttributes(array(
                'password',
                'salt'
            ));

            $userInfoArray = $normalizer->normalize($userInfo);

            $response->setData($userInfoArray);
            $response->setStatusCode(JsonResponse::HTTP_OK);
        }
        else {
            $response->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
        }

        return $response;
    }

    /**
     * Update user data
     *
     * @author Adir Kuhn <adirkuhn@gmail.com>
     *
     * @return Response JsonResponse Updated Resource
     */
    public function userUpdateAction(Request $request)
    {
        //get post data
        $newUserData = $request->request->all();

        //get entity manager and find user
        $entityManager = $this->getDoctrine()->getManager();
        $userToBeUpdated = $entityManager->find('UserBundle:User', $newUserData['id']);

        //remove id from data
        unset($newUserData['id']);

        $response = new JsonResponse();
        if($userToBeUpdated) {

            //create form and set new data
            $form = $this->createForm(new UserType(), $userToBeUpdated);
            $form->submit($newUserData, false);

            if($form->isValid()) {

                //save data
                $entityManager->flush();

                $normalizer = new GetSetMethodNormalizer();
                $normalizer->setIgnoredAttributes(array(
                    'password',
                    'salt'
                ));

                $userUpdatedData = $normalizer->normalize($userToBeUpdated);

                $response->setData($userUpdatedData);
                $response->setStatusCode(JsonResponse::HTTP_OK);
            }
            else {
                //cant update this resource
                $response->setData('Can\'t update this resource.');
                $response->setStatusCode(JsonResponse::HTTP_CONFLICT);
            }
        }
        else {
            $response->setData('Resource not found.');
            $response->setStatusCode(JsonResponse::HTTP_NOT_FOUND);
        }

        return $response;
    }


}
