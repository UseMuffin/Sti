<?php
declare(strict_types=1);

namespace Muffin\Sti\TestApp\Model\Table;

use Cake\Validation\Validator;

class AssistantChefsTable extends CooksTable
{
    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('name', 'assistant chef');

        return $validator;
    }
}
