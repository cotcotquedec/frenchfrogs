<?php namespace FrenchFrogs\Laravel\Mail;

class Mailable extends \Illuminate\Mail\Mailable implements \FrenchFrogs\Laravel\Contracts\Mail\MailablePreview
{

    /**
     *
     * Preview the message using the given mailer.
     *
     * @return void
     */
    public function previewHtml()
    {
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