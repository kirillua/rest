<?php
/**
 * Created by PhpStorm.
 * User: kirillua
 * Date: 24.06.18
 * Time: 15:01
 */

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use AppBundle\Entity\User;

class UserController extends FOSRestController
{
    /**
     *
     * @return array|View
     * @Rest\Get("/users")
     */
    public function getUserList()
    {
        $list = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();
        if ($list === null) {
            return new View("there are no users exist", Response::HTTP_NOT_FOUND);
        }
        return $list;
    }

    /**
     *
     * @param Request $request
     * @return View
     * @Rest\Post("/user/add")
     */

    public function addUser(Request $request)
    {
        $user = new User();

        $login = $request->get('login');
        $name = $request->get('name');

        $user->setLogin($login);
        $user->setName($name);

        $validator = $this->get('validator');
        $errors = $validator->validate($user);

        if(count($errors) > 0) {
            return new View(['errors' => $errors], Response::HTTP_NOT_FOUND);
        }

        $exist_user = $this->getDoctrine()
                           ->getRepository(User::class)
                           ->findOneBy(['login' => $login]);

        if($exist_user) {
            return new View(['status' => 'User already exists']);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return new View(['status' => "User Added Successfully"], Response::HTTP_OK);
    }

    /**
     *
     * @param Request $request
     * @return View
     * @Rest\Put("/user/edit")
     */
    public function editUser( Request $request)
    {
        $data = new User();

        $name = $request->get('name');
        $login = $request->get('login');
        $data->setName($name);
        $data->setLogin($login);

        $validator = $this->get('validator');
        $errors = $validator->validate($data);

        if(count($errors) > 0 ) {
            return new View(['errors' => $errors], Response::HTTP_NOT_FOUND);
        }


        $user = $this->getDoctrine()
                     ->getRepository(User::class)
                     ->findOneBy(['login' => $login]);

        if (empty($user)) {
            return new View(['status' => "user not found"], Response::HTTP_NOT_FOUND);
        }

        $user->setName($name);
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View(['status' => 'User ' . $login . ' update successfully'], Response::HTTP_OK);
    }

    /**
     * @param $login
     * @return View
     * @Rest\Delete("/user/delete/{login}")
     */
    public function deleteUser($login)
    {
        $user = $this->getDoctrine()
                     ->getRepository(User::class)
                     ->findOneBy(['login' => $login]);

        if (empty($user)) {
            return new View(['status' => "user not found"], Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return new View(['status' => 'User with login: ' . $login . ' deleted successfully'], Response::HTTP_OK);
    }
}