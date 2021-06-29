<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\User;
use App\Entity\Category;
use Cocur\Slugify\Slugify;
use App\Entity\GalleryVente;
use App\Entity\GalleryEchange;
use App\Entity\ArtisticWorkVente;
use App\Entity\ArtisticWorkEchange;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AppFixtures implements FixtureInterface
{

    protected $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        $faker =  Faker\Factory::create('fr_FR');
        $slugify = new Slugify();
        $categories = [];
        $users = [];

        $cat = ['Dessin', 'Peinture', 'Sculpture', 'Modelage', 'Art-numérique', 'Photographie', 'Céramique', 'Mosaique', 'Collectionneur'];
        foreach ($cat as $name) {
            $category = new Category();
            $category->setName($name);
            $slug = $slugify->slugify($category->getName());
            $category->setSlug($name);
            $manager->persist($category);
            $categories[] = $category;
        }
        $villes = ['Paris', 'Lyon', 'Rouen', 'Nice', 'Nice'];
        foreach ($villes as $city) {

            for ($h = 1; $h <= 5; $h++) {

                $user = new User();
                $hash = $this->encoder->hashPassword($user, 'password');


                $user->setFirstName($faker->firstNameMale());
                $user->setLastName($faker->lastName());
                $user->setEmail("email+" . $h . "@email.com");
                $slug = $slugify->slugify($user->getFirstName() . ' ' . $user->getLastName());
                $user->setSlug($slug);
                $user->setLocation($city);


                $user->setNiveau(1);
                $user->setAvatar('avatarDefaut.jpg'); // la je donne le nom de l'avatar
                $user->setAvatarFile(new File('public/images/avatars/avatardefaut.jpg')); // la son fichier


                $user->setPassword($hash);




                foreach ($categories as $category) {
                    $user->getCategories($categories)->add($category);
                }

                $user->setRegisteredAt($faker->dateTimeBetween($startDate = '-6 months', $endDate = 'now'));
            }
            $users[] = $user;
            $villes[] = $city;

            $manager->persist($user);
        };

        foreach ($categories as $category) {
            foreach ($users as $user) {
                for ($g = 0; $g <= 1; $g++) {
                    $galleryEchange = new GalleryEchange();
                    $galleryEchange->setName($faker->name);
                    $slug = $slugify->slugify($galleryEchange->getName());
                    $galleryEchange->setSlug($slug);
                    $galleryEchange->setCategory($category);
                    $galleryEchange->setUser($user);
                    $manager->persist($galleryEchange);

                    for ($a = 1; $a <= 2; $a++) {

                        $artWorkEchange = new ArtisticWorkEchange();
                        $artWorkEchange->setName($faker->name);
                        $artWorkEchange->setGalleryEchange($galleryEchange);
                        $artWorkEchange->setSlug($faker->name);
                        $artWorkEchange->setCategory($category);
                        $artWorkEchange->setPicture('avatarDefaut.jpg');
                        $artWorkEchange->setPictureFile(new File('public/images/avatars/avatardefaut.jpg'));
                        $artWorkEchange->setDescription($faker->text);
                        $artWorkEchange->setCreatedAt($faker->dateTimeBetween($startDate = '-6 months', $endDate = 'now'));
                        $artWorkEchange->setUpdatedAt($faker->dateTimeBetween($startDate = '-6 months', $endDate = 'now'));
                        $manager->persist($artWorkEchange);
                    }
                    for ($g = 0; $g <= 1; $g++) {
                        $galleryVente = new GalleryVente();
                        $galleryVente->setName($faker->name);
                        $slug = $slugify->slugify($galleryVente->getName());
                        $galleryVente->setSlug($slug);
                        $galleryVente->setCategory($category);
                        $galleryVente->setUser($user);
                        $manager->persist($galleryVente);

                        for ($b = 1; $b <= 2; $b++) {

                            $artWorkVente = new ArtisticWorkVente();

                            $artWorkVente->setName($faker->name);
                            $artWorkVente->setGalleryVente($galleryVente);
                            $artWorkVente->setSlug($faker->name);
                            $artWorkVente->setCategory($category);
                            $artWorkVente->setPicture('avatarDefaut.jpg');
                            $artWorkVente->setPictureFile(new File('public/images/avatars/avatardefaut.jpg'));
                            $artWorkVente->setDescription($faker->text);
                            $artWorkVente->setCreatedAt($faker->dateTimeBetween($startDate = '-6 months', $endDate = 'now'));
                            $artWorkVente->setUpdatedAt($faker->dateTimeBetween($startDate = '-6 months', $endDate = 'now'));
                            $manager->persist($artWorkVente);
                        }
                    }
                }
            }
        }

        $manager->flush();
    }
}
