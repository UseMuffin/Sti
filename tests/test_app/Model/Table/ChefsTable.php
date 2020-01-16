<?php
namespace Muffin\Sti\TestApp\Model\Table;

use Cake\Validation\Validator;

class ChefsTable extends CooksTable
{
    public function validationDefault(Validator $validator)
    {
        if (method_exists($validator, 'notEmptyString')) {
            $validator->notEmptyString('name', 'chef');
        } else {
            $validator->notEmpty('name', 'chef');
        }

        return $validator;
    }
}
