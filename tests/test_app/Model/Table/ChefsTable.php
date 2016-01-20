<?php
namespace Muffin\Sti\TestApp\Model\Table;

use Cake\Validation\Validator;

class ChefsTable extends CooksTable
{
    public function validationDefault(Validator $validator)
    {
        $validator->notEmpty('name', 'chef');
        return $validator;
    }
}
