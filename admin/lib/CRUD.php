<?php
/**
 * Undocumented
 */
class CRUD extends View_CRUD
{	
	    /**
     * Configures necessary components when CRUD is in the editing mode.
     *
     * @param array $fields List of fields for add form
     *
     * @return void|Model If model, then bail out, no greed needed
     */
    protected function configureEdit($fields = null)
    {
        // We are actually in the frame!
        if ($this->isEditing('edit')) {
            $m = $this->form->setModel($this->model, $fields);
            $m->load($this->id);
            $this->form->addSubmit();
            $this->form->onSubmit(array($this, 'formSubmit'));

            return $m;
        } elseif ($this->isEditing()) {
            return;
        }

        $this
            ->virtual_page
            ->addColumn(
                'edit',
                'Editing '.$this->entity_name,
                array('descr' => 'Edit', 'icon' =>' fa fa-pencil'),
                $this->grid
            );
    }

	protected function configureAdd($fields = null)
    {
        // We are actually in the frame!
        if ($this->isEditing('add')) {
            $this->model->unload();
            $m = $this->form->setModel($this->model, $fields);
            $this->form->addSubmit('Add');
            $this->form->onSubmit(array($this, 'formSubmit'));

            return $m;
        } elseif ($this->isEditing()) {
            return;
        }

        // Configure Add Button on Grid and JS
        $this->add_button->js('click')->univ()
            ->frameURL(
                $this->app->_(
                    $this->entity_name === false
                    ? 'New Record'
                    : 'Adding new '.$this->entity_name
                ),
                $this->virtual_page->getURL('add'),
                $this->frame_options
            );

        if ($this->entity_name !== false) {
            $this->add_button->setHTML('<i class="fa fa-plus"></i> Add '.htmlspecialchars($this->entity_name));
        }
    }
}
