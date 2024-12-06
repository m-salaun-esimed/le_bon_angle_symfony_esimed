<?php
        namespace App\Entity;
        use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
        use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
        use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
        use ApiPlatform\Metadata\ApiFilter;
        use ApiPlatform\Metadata\ApiResource;
        use ApiPlatform\Metadata\Get;
        use ApiPlatform\Metadata\GetCollection;
        use App\Repository\AdvertRepository;
        use Doctrine\Common\Collections\ArrayCollection;
        use Doctrine\Common\Collections\Collection;
        use Doctrine\DBAL\Types\Types;
        use Doctrine\ORM\Mapping as ORM;
        use Symfony\Component\Serializer\Attribute\Groups;
        use ApiPlatform\Metadata\Post;

        #[ORM\Entity(repositoryClass: AdvertRepository::class)]
        #[ApiResource(
            operations: [
                new GetCollection(),
                new Get(),
                new Post(
                    normalizationContext: ['groups' => ['advert:read', 'picture:read']],
                    denormalizationContext: ['groups' => ['advert:write']]
                )
            ]
        )]
        #[ApiFilter(OrderFilter::class, properties: ['publishedAt', 'price'], arguments: ['orderParameterName' => 'order'])]
        #[ApiFilter(SearchFilter::class, properties: ['category' => 'exact'])]
        #[ApiFilter(RangeFilter::class, properties: ['price'])]
        class Advert
        {
            #[ORM\Id]
            #[ORM\GeneratedValue]
            #[ORM\Column]
            private ?int $id = null;    

            #[ORM\Column(length: 255)]
            #[Groups(['advert:read', 'advert:write'])]
            private ?string $title = null;

            #[ORM\Column(type: Types::TEXT)]
            #[Groups(['advert:read', 'advert:write'])]
            private ?string $content = null;

            #[ORM\Column(length: 255)]
            #[Groups(['advert:read', 'advert:write'])]
            private ?string $author = null;

            #[ORM\Column(length: 255)]
            #[Groups(['advert:read', 'advert:write'])]
            private ?string $email = null;

            #[ORM\ManyToOne(inversedBy: 'adverts')]
            #[Groups(['advert:read', 'advert:write'])]
            private ?Category $category = null;

            #[ORM\Column]
            #[Groups(['advert:read', 'advert:write'])]
            private ?float $price = null;

            #[ORM\Column(length: 255)]
            #[Groups(['advert:read', 'advert:write'])]
            private ?string $state = 'draft';

            #[ORM\Column(type: 'datetime_immutable', nullable: true)]
            #[Groups(['advert:read', 'advert:write'])]
            private ?\DateTimeImmutable $creatadAt = null;

            #[ORM\Column(type: 'datetime_immutable', nullable: true)]
            #[Groups(['advert:read', 'advert:write'])]
            private ?\DateTimeImmutable $publishAt = null;

            /**
             * @var Collection<int, Picture>
             */
            #[ORM\OneToMany(targetEntity: Picture::class, mappedBy: 'advert')]
            private Collection $pictures;

            public function __construct()
            {
                $this->pictures = new ArrayCollection();
            }

            public function __toString(): string
            {
                return $this->title; // Retourne le titre de l'annonce
            }

            public function getId(): ?int
            {
                return $this->id;
            }

            public function getTitle(): ?string
            {
                return $this->title;
            }

            public function setTitle(string $title): static
            {
                $this->title = $title;

                return $this;
            }

            public function getContent(): ?string
            {
                return $this->content;
            }

            public function setContent(string $content): static
            {
                $this->content = $content;

                return $this;
            }

            public function getAuthor(): ?string
            {
                return $this->author;
            }

            public function setAuthor(string $author): static
            {
                $this->author = $author;

                return $this;
            }

            public function getEmail(): ?string
            {
                return $this->email;
            }

            public function setEmail(string $email): static
            {
                $this->email = $email;

                return $this;
            }

            public function getCategory(): ?Category
            {
                return $this->category;
            }

            public function setCategory(?Category $category): static
            {
                $this->category = $category;

                return $this;
            }

            public function getPrice(): ?float
            {
                return $this->price;
            }

            public function setPrice(float $price): static
            {
                $this->price = $price;

                return $this;
            }

            public function getState(): ?string
            {
                return $this->state;
            }

            public function setState(string $state): static
            {
                $this->state = $state;

                return $this;
            }

            public function getCreatadAt(): ?\DateTimeImmutable
            {
                return $this->creatadAt;
            }

            public function setCreatadAt(\DateTimeImmutable $creatadAt): static
            {
                $this->creatadAt = $creatadAt;

                return $this;
            }

            public function getPublishAt(): ?\DateTimeImmutable
            {
                return $this->publishAt;
            }

            public function setPublishAt(?\DateTimeImmutable $publishAt): self
            {
                $this->publishAt = $publishAt;

                return $this;
            }

            /**
             * @return Collection<int, Picture>
             */
            public function getPictures(): Collection
            {
                return $this->pictures;
            }

            public function addPicture(Picture $picture): static
            {
                if (!$this->pictures->contains($picture)) {
                    $this->pictures->add($picture);
                    $picture->setAdvert($this);
                }

                return $this;
            }

            public function removePicture(Picture $picture): static
            {
                if ($this->pictures->removeElement($picture)) {
                    // set the owning side to null (unless already changed)
                    if ($picture->getAdvert() === $this) {
                        $picture->setAdvert(null);
                    }
                }

                return $this;
            }
        }
