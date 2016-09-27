<?php namespace FrenchFrogs\Laravel\Contracts\Mail;


interface MailablePreview
{
    public function previewHtml();
    public function previewSubject();
    public function previewTo();
    public function previewFrom();
    public function previewPlain();
}
