<?php


namespace App\Model\Document;


class MarkDownResult
{
    /**
     * @var array|null
     */
    protected $config = [];
    /**
     * @var string|null
     */
    protected $html = null;

    protected $markdown;

    /**
     * @return array|null
     */
    public function getConfig(): ?array
    {
        return $this->config;
    }

    /**
     * @param array|null $config
     */
    public function setConfig(?array $config): void
    {
        $this->config = $config;
    }

    /**
     * @return string|null
     */
    public function getHtml(): ?string
    {
        return $this->html;
    }

    /**
     * @param string|null $html
     */
    public function setHtml(?string $html): void
    {
        $this->html = $html;
    }

    /**
     * @return mixed
     */
    public function getMarkdown()
    {
        return $this->markdown;
    }

    /**
     * @param mixed $markdown
     */
    public function setMarkdown($markdown): void
    {
        $this->markdown = $markdown;
    }

    function toArray()
    {
        return [
            'config'=>$this->config,
            'plainText'=>$this->markdown,
            'html'=>$this->html
        ];
    }
}