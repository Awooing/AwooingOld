<?php
declare(strict_types=1);

namespace Awoo\Models;


class CdnModel
{

    /** @var \SpacesConnect */
    private $cdn;

    public function __construct()
    {
        $this->cdn = new \SpacesConnect("O3IVUIW3PBL2JD556YHR", "maUNhIIoxOeM+Q47Xc1CZ1R9EZ5QXTd9Ol8fNbK0ZLE", "awooing", "fra1");
    }

    /**
     * @param string $folderName
     * @return array|null
     */
    public function getFolderContents(string $folderName): ?array
    {
        return $this->cdn->ListObjects($folderName);
    }

    /**
     * @return array|null
     */
    public function getAwooImages(): ?array
    {
        return $this->getFolderContents("i");
    }

    /**
     * @param int $i
     * @return array|null
     */
    public function getAwoo(int $i): ?array
    {
        return $this->getAwooImages()[$i];
    }

    /**
     * @return array|null
     */
    public function getRandomAwoo(): ?array
    {
        $awoos = $this->getAwooImages();
        return $awoos[array_rand($awoos)];
    }

    /**
     * @param array $file
     * @return mixed
     */
    public function getFileKey(array $file)
    {
        return $file['Key'];
    }

    /**
     * @param array $file
     * @return mixed
     */
    public function getLastModified(array $file)
    {
        return $file['LastModified'];
    }

    /**
     * @param array $file
     * @return mixed
     */
    public function getSize(array $file)
    {
        return $file['Size'];
    }

    /**
     * @return string
     */
    public function getDataUrl(): string
    {
        return "https://cdn.awooing.moe/";
    }
}