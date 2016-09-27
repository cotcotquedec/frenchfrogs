<?php namespace FrenchFrogs\Laravel\Mail;

class Mailable extends \Illuminate\Mail\Mailable implements \FrenchFrogs\Laravel\Contracts\Mail\MailablePreview
{
    /**
     * @var
     */
    protected $uuid;

    /**
     * Pixel
     *
     * @var string
     */
    public $pixel = '';

    /**
     * Set UUID
     *
     * @return mixed
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Return uuid
     *
     * @param $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = \uuid('hex', $uuid);
        return $this;
    }


    /**
     * Nom de la route
     *
     * @var string
     */
    static protected $pixelRoute = 'mail.pixel';

    /**
     * Getter for $pixelRoute
     *
     * @return string
     */
    static public function getPixelRoute()
    {
        return static::$pixelRoute;
    }

    /**
     * Setter for $pixelRoute
     *
     * @param $route
     */
    static function setPixelRoute($route)
    {
        static::$pixelRoute = $route;
    }


    /**
     * Render pixel for tracking
     *
     * @param array $query
     * @return string
     */
    public function renderPixel($query = [])
    {
        // route principale
        $url = route(static::getPixelRoute(), $this->getUuid());

        // gestion de la query
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        $this->pixel = html('img', ['src' => $url, 'width' => 1, 'height' => 1]);
    }

    /**
     *
     * Preview the message using the given mailer.
     *
     * @return void
     */
    public function previewHtml()
    {
        $this->pixel = '';
        return \view($this->view, $this->buildViewData());
    }

    /**
     *
     * Rendu du mail en text
     *
     * return string
     *
     */
    public function previewPlain()
    {
        return \view($this->textView, $this->buildViewData());
    }

    /**
     * Preview des destinataire
     *
     * @return string
     */
    public function previewTo()
    {
        $to = [];
        foreach ($this->to as $row) {
            $to[]= sprintf('%s < %s >',$row['name'], $row['address']);
        }
        return implode(',', $to);
    }

    /***
     * Preview de l'expediteur
     *
     * @return string
     */
    public function previewFrom()
    {
        $from = [];
        foreach ($this->from as $row) {
            $from[]= sprintf('%s < %s >',$row['name'], $row['address']);
        }
        return implode(',', $from);
    }


    /**
     * Getter for $subjecty
     *
     * @return string
     */
    public function previewSubject()
    {
        return $this->subject;
    }
}