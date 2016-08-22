<?php namespace FrenchFrogs\App\Http\Controllers;

/**
 * Class AclController
 *
 * Gestion des droits
 *
 * @package FrenchFrogs\Acl\Http\Controllers
 */
trait ReferenceController
{
    protected $permission;

    /**
     * Build user table polliwog
     *
     * @return \FrenchFrogs\Table\Table\Table
     */
    static public function reference()
    {
        $query = \query('reference')->orderBy('collection')->orderBy('name');


        $table = \table($query);
        $table->setConstructor(static::class, __FUNCTION__)->enableRemote()->enableDatatable();
        $table->useDefaultPanel('Liste des références')->getPanel();
        $table->addText('reference_id', 'ID')->setStrainerText('refernce_id');
        $table->addText('collection', 'Collection')->setStrainerText('collection');
        $table->addText('name', 'Nom')->setStrainerText('name');
        $table->addText('deleted_at', 'Désactive le');
        $table->setSearch('reference_id');
        return $table;
    }


    /**
     * List all references
     *
     * @return \Illuminate\View\View
     */
    public function getIndex()
    {
        \ruler()->check($this->permission);
        return basic('Références', static::reference());
    }
}