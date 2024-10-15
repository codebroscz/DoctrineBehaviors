<?php

declare(strict_types=1);

use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface;
use Knp\DoctrineBehaviors\EventSubscriber\BlameableEventSubscriber;
use Knp\DoctrineBehaviors\EventSubscriber\LoggableEventSubscriber;
use Knp\DoctrineBehaviors\EventSubscriber\SluggableEventSubscriber;
use Knp\DoctrineBehaviors\EventSubscriber\SoftDeletableEventSubscriber;
use Knp\DoctrineBehaviors\EventSubscriber\TimestampableEventSubscriber;
use Knp\DoctrineBehaviors\EventSubscriber\TranslatableEventSubscriber;
use Knp\DoctrineBehaviors\EventSubscriber\TreeEventSubscriber;
use Knp\DoctrineBehaviors\EventSubscriber\UuidableEventSubscriber;
use Knp\DoctrineBehaviors\Tests\DatabaseLoader;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\UserEntity;
use Knp\DoctrineBehaviors\Tests\Provider\EntityUserProvider;
use Knp\DoctrineBehaviors\Tests\Provider\TestLocaleProvider;
use Knp\DoctrineBehaviors\Tests\Provider\TestUserProvider;
use Psr\Log\Test\TestLogger;

return static function (): \Psr\Container\ContainerInterface {
    $container = new \UMA\DIC\Container([

    ]);

//    $parameters = $containerConfigurator->parameters();

    $_ENV['DB_ENGINE'] = 'pdo_sqlite';
    $_ENV['DB_HOST'] = 'localhost';
    $_ENV['DB_NAME'] = 'orm_behaviors_test';
    $_ENV['DB_USER'] = 'root';
    $_ENV['DB_PASSWD'] = '';
    $_ENV['DB_MEMORY'] = 'true';
    $_ENV['locale'] = 'en';

    $container->set(TestLogger::class, new TestLogger());

    $container->set(LocaleProviderInterface::class, new TestLocaleProvider());

//    $container->set(UserProviderInterface::class, static function(Psr\Container\ContainerInterface $c): UserProviderInterface {
//        /** @var \Doctrine\ORM\EntityManagerInterface $em */
//        $em = $c->get(\Doctrine\ORM\EntityManagerInterface::class);
//
//        $evm = $em->getEventManager();
////        return new TestUserProvider();
//    });

    $container->set(\Doctrine\ORM\Configuration::class, static function(Psr\Container\ContainerInterface $c): \Doctrine\ORM\Configuration {
        $config = new \Doctrine\ORM\Configuration();
        $config->setMetadataDriverImpl(new \Doctrine\ORM\Mapping\Driver\AttributeDriver([__DIR__ . '/../../tests/Fixtures/Entity']));
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(__DIR__ . '/../../tests/Proxies');
        $config->setProxyNamespace('Proxies');

        return $config;
    });

    $container->set(\Doctrine\DBAL\Connection::class, static function(Psr\Container\ContainerInterface $c): \Doctrine\DBAL\Connection {
        $config = new \Doctrine\DBAL\Configuration();

        return \Doctrine\DBAL\DriverManager::getConnection([
            'driver' => $_ENV['DB_ENGINE'],
            'host' => $_ENV['DB_HOST'],
            'dbname' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWD'],
            'memory' => $_ENV['DB_MEMORY'],
        ], $config);
    });

    $container->set(\Doctrine\ORM\EntityManagerInterface::class, static function(Psr\Container\ContainerInterface $c): \Doctrine\ORM\EntityManagerInterface {
        $evm = new \Doctrine\Common\EventManager();

        $entityManager = new \Doctrine\ORM\EntityManager(
            $c->get(\Doctrine\DBAL\Connection::class),
            $c->get(\Doctrine\ORM\Configuration::class),
            $evm,
        );

        $c->set(UserProviderInterface::class, new EntityUserProvider($entityManager));

        $evm->addEventSubscriber(new BlameableEventSubscriber(
            $c->get(UserProviderInterface::class),
            $entityManager,
            UserEntity::class,
        ));

        $evm->addEventSubscriber(new LoggableEventSubscriber(
            $c->get(TestLogger::class),
        ));

        $evm->addEventSubscriber(new SluggableEventSubscriber(
            $entityManager,
            new \Knp\DoctrineBehaviors\Repository\DefaultSluggableRepository($entityManager),
        ));

        $evm->addEventSubscriber(new SoftDeletableEventSubscriber());

        $evm->addEventSubscriber(new TimestampableEventSubscriber('datetime'));

        $evm->addEventSubscriber(new TranslatableEventSubscriber(
            $c->get(LocaleProviderInterface::class),
            'LAZY',
            'LAZY',
        ));

        $evm->addEventSubscriber(new TreeEventSubscriber());

        $evm->addEventSubscriber(new UuidableEventSubscriber());

        return $entityManager;
    });

    $container->set(DatabaseLoader::class, static function(Psr\Container\ContainerInterface $c): DatabaseLoader {
        return new DatabaseLoader($c->get(\Doctrine\ORM\EntityManagerInterface::class), $c->get(\Doctrine\DBAL\Connection::class));
    });

    $container->set(LoggableEventSubscriber::class, static function(Psr\Container\ContainerInterface $c): LoggableEventSubscriber {
        return new LoggableEventSubscriber($c->get(TestLogger::class));
    });

//    $containerConfigurator->extension('doctrine', [
//        'orm' => [
//            'auto_mapping' => true,
//            'mappings' => [
//                [
//                    'name' => 'DoctrineBehaviors',
//                    'type' => 'attribute',
//                    'prefix' => 'Knp\DoctrineBehaviors\Tests\Fixtures\Entity\\',
//                    'dir' => __DIR__ . '/../../tests/Fixtures/Entity',
//                    'is_bundle' => false,
//                ],
//            ],
//        ],
//    ]);

    return $container;
};
