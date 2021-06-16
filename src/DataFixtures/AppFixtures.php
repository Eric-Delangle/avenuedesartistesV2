<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\User;
use App\Entity\Gallery;
use App\Entity\Category;
use Cocur\Slugify\Slugify;
use App\Entity\ArtisticWork;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\HttpFoundation\File\File;


class AppFixtures extends Fixture
{
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
                $user->setFirstName($faker->firstNameMale());
                $user->setLastName($faker->lastName());
                $user->setEmail("email+" . $h . "@email.com");
                $slug = $slugify->slugify($user->getFirstName());
                $user->setSlug($slug);
                $user->setLocation($city);
                $user->setPassword("password");
                $user->setNiveau(1);
                $user->setAvatar('avatarDefaut.jpg'); // la je donne le nom de l'avatar
                $user->setAvatarFile(new File('public/images/avatars/avatardefaut.jpg')); // la son fichier

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
                    $gallery = new Gallery();
                    $gallery->setName($faker->name);
                    $slug = $slugify->slugify($gallery->getName());
                    $gallery->setSlug($slug);
                    $gallery->setCategory($category);
                    $gallery->setUser($user);
                    $manager->persist($gallery);

                    for ($a = 1; $a <= 2; $a++) {

                        $artWork = new ArtisticWork();
                        $artWork->setName($faker->name);
                        $artWork->setGallery($gallery);
                        $artWork->setSlug($faker->name);
                        $artWork->setCategory($category);
                        $artWork->setPicture('avatarDefaut.jpg');
                        $artWork->setPictureFile(new File('public/images/avatars/avatardefaut.jpg'));
                        $artWork->setDescription($faker->text);
                        $artWork->setCreatedAt($faker->dateTimeBetween($startDate = '-6 months', $endDate = 'now'));
                        $artWork->setUpdatedAt($faker->dateTimeBetween($startDate = '-6 months', $endDate = 'now'));
                        $manager->persist($artWork);
                    }
                }
            }
        }

        $manager->flush();
    }
}
