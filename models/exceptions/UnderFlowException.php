<?php
namespace app\models\exceptions;

use yii\base\Exception;

class UnderFlowException extends Exception
{
    protected $message = 'under flow value.';
}
