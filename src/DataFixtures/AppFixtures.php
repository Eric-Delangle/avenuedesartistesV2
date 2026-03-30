<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Cocur\Slugify\Slugify;
use App\Entity\Category;
use App\Entity\User;
use App\Entity\ArtisticWork;
use App\Entity\Gallery;
use Symfony\Component\HttpFoundation\File\File;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $slugify = new Slugify();
        $categories = [];
        $users = [];

        // Catégories
        $cat = ['Dessin', 'Peinture', 'Sculpture', 'Modelage', 'Art-numérique', 'Photographie', 'Céramique', 'Mosaique', 'Collectionneur'];
        foreach ($cat as $name) {
            $category = new Category();
            $category->setName($name);
            $category->setSlug($slugify->slugify($name)); // bug corrigé : utilisait $name au lieu du slug
            $manager->persist($category);
            $categories[] = $category;
        }

        // Users
        $villes = ['Paris', 'Lyon', 'Rouen', 'Nice', 'Marseille'];
        foreach ($villes as $city) {
            for ($h = 1; $h <= 5; $h++) {
                $user = new User();
                $user->setFirstName($faker->firstNameMale());
                $user->setLastName($faker->lastName());
                $user->setEmail($faker->unique()->safeEmail()); // emails uniques pour éviter les doublons
                $user->setSlug($slugify->slugify($user->getFirstName() . '-' . $faker->unique()->numberBetween(1, 9999)));
                $user->setLocation($city);
                $user->setPassword('password');
                $user->setNiveau(1);
                $user->setAvatar('avatarDefaut.jpg');
                $user->setAvatarFile(new File('public/images/artisticWorks/avatarDefaut.jpg'));
                $user->setRegisteredAt($faker->dateTimeBetween('-6 months', 'now'));

                // bug corrigé : getCategories() ne prend pas d'argument
                foreach ($categories as $category) {
                    $user->getCategories()->add($category);
                }

                $manager->persist($user);
                $users[] = $user; // bug corrigé : était en dehors du foreach
            }
        }

        // Galeries & œuvres
        foreach ($categories as $category) {
            foreach ($users as $user) {
                for ($g = 0; $g <= 1; $g++) {
                    $gallery = new Gallery();
                    $gallery->setName($faker->words(3, true));
                    $gallery->setSlug($slugify->slugify($gallery->getName()));
                    $gallery->setCategory($category);
                    $gallery->setUser($user);
                    $manager->persist($gallery);

                    for ($a = 1; $a <= 2; $a++) {
                        $artWork = new ArtisticWork();
                        $artWork->setName($faker->words(3, true));
                        $artWork->setGallery($gallery);
                        $artWork->setSlug($slugify->slugify($artWork->getName() . '-' . $faker->unique()->numberBetween(1, 99999)));
                        $artWork->setCategory($category);
                        $artWork->setPicture('avatarDefaut.jpg');
                        $artWork->setPictureFile(new File('public/images/artisticWorks/avatarDefaut.jpg'));
                        $artWork->setDescription($faker->text());
                        $artWork->setCreatedAt($faker->dateTimeBetween('-6 months', 'now'));
                        $artWork->setUpdatedAt($faker->dateTimeBetween('-6 months', 'now'));

                        // Champs marketplace — distribution réaliste
                        $listingType = $faker->randomElement(['none', 'none', 'sale', 'exchange', 'both']);
                        $artWork->setListingType($listingType);
                        if (in_array($listingType, ['sale', 'both'])) {
                            $artWork->setPrice((string) $faker->randomFloat(2, 20, 1500));
                            $artWork->setCurrency('EUR');
                        }
                        if (in_array($listingType, ['exchange', 'both'])) {
                            $artWork->setExchangeDescription($faker->sentence(10));
                        }
                        if ($listingType !== 'none') {
                            $artWork->setStatus($faker->randomElement(['available', 'available', 'available', 'reserved', 'sold']));
                        }

                        $manager->persist($artWork);
                    }
                }
            }
        }

        $manager->flush();
    }
}