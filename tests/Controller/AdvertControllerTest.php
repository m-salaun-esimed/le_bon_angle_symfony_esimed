<?php

namespace App\Tests\Controller;

use App\Entity\Advert;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdvertControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/advert/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Advert::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Advert index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'advert[title]' => 'Testing',
            'advert[content]' => 'Testing',
            'advert[author]' => 'Testing',
            'advert[email]' => 'Testing',
            'advert[price]' => 'Testing',
            'advert[state]' => 'Testing',
            'advert[creatadAt]' => 'Testing',
            'advert[publishAt]' => 'Testing',
            'advert[category]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Advert();
        $fixture->setTitle('My Title');
        $fixture->setContent('My Title');
        $fixture->setAuthor('My Title');
        $fixture->setEmail('My Title');
        $fixture->setPrice('My Title');
        $fixture->setState('My Title');
        $fixture->setCreatadAt('My Title');
        $fixture->setPublishAt('My Title');
        $fixture->setCategory('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Advert');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Advert();
        $fixture->setTitle('Value');
        $fixture->setContent('Value');
        $fixture->setAuthor('Value');
        $fixture->setEmail('Value');
        $fixture->setPrice('Value');
        $fixture->setState('Value');
        $fixture->setCreatadAt('Value');
        $fixture->setPublishAt('Value');
        $fixture->setCategory('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'advert[title]' => 'Something New',
            'advert[content]' => 'Something New',
            'advert[author]' => 'Something New',
            'advert[email]' => 'Something New',
            'advert[price]' => 'Something New',
            'advert[state]' => 'Something New',
            'advert[creatadAt]' => 'Something New',
            'advert[publishAt]' => 'Something New',
            'advert[category]' => 'Something New',
        ]);

        self::assertResponseRedirects('/advert/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitle());
        self::assertSame('Something New', $fixture[0]->getContent());
        self::assertSame('Something New', $fixture[0]->getAuthor());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getPrice());
        self::assertSame('Something New', $fixture[0]->getState());
        self::assertSame('Something New', $fixture[0]->getCreatadAt());
        self::assertSame('Something New', $fixture[0]->getPublishAt());
        self::assertSame('Something New', $fixture[0]->getCategory());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Advert();
        $fixture->setTitle('Value');
        $fixture->setContent('Value');
        $fixture->setAuthor('Value');
        $fixture->setEmail('Value');
        $fixture->setPrice('Value');
        $fixture->setState('Value');
        $fixture->setCreatadAt('Value');
        $fixture->setPublishAt('Value');
        $fixture->setCategory('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/advert/');
        self::assertSame(0, $this->repository->count([]));
    }
}
