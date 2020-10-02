<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

trait Id
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="NONE")
     * @ApiPlatform\Core\Annotation\ApiProperty(
     *     identifier=true,
     *     openapiContext={
     *       "example": "00000000-0000-0000-0000-000000000000"
     *     }
     * )
     * @Symfony\Component\Serializer\Annotation\Groups({"general:read"})
     */
    protected UuidInterface $id;

    public function getId(): string
    {
        return $this->id->toString();
    }
}
