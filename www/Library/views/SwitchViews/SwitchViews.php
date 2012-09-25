<?php
/**
 * Переключатель вариантов отображения (исполнения)
 *
 * Содержит варианты, значения которых - условие исполнения.
 * Условие исполнение - это uri отображаемого объекта или uri прототипов отображаемого объекта.
 * Может оказаться несколько вариантов с выполняемым условием, но выбирается только первый вариант.
 * В качестве вараинтов предполагается использовать объекты SwithCase.
 *
 * @version 1.0
 */
namespace Library\views\SwitchViews;

use Boolive\values\Rule,
    Boolive\input\Input,
    Library\views\Widget\Widget;

class SwitchViews extends Widget
{
    protected $_cases;

    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::entity()->default(null)->required(),
                    )
                )
            )
        );
    }

    protected function initInputChild($input)
    {
        parent::initInputChild(array_replace_recursive($input, $this->_input));
    }

    public function work($v = array())
    {
        // Все варианты отображений для последующего поиска нужного
        $cases = $this->getCases();
        $obj = $this->_input['REQUEST']['object'];
        $v['object'] = null;
        //$uri = $obj['uri'];
        $cnt = count($cases);
        $case = null;
        for ($i = 0; $i < $cnt; ++$i){
            if ($cases[$i] instanceof \Library\views\SwitchCase\SwitchCase){
                $uri = $cases[$i]->getValue();
                if ($uri=='all'){
                    $case = $cases[$i];
                }else{
                    $obj = $this->_input['REQUEST']['object'];
                    while ($obj && !$case){
                        if ($obj['uri'] == $uri){
                            $case = $cases[$i];
                        }else{
                            $obj = $obj->proto();
                        }
                    }
                }
            }
            if ($case) $i = $cnt;
        }
        if ($case){
            $v['object'] = $case->start($this->_commands, $this->_input_child);
            if ($v['object'] != null){
                $this->_input_child['previous'] = true;
            }
        }
        return parent::work($v);
    }

    protected function getCases(){
        if (!isset($this->_cases)){
            $this->_cases = $this->findAll(array('where'=>'is_history=0 and is_delete=0'), false, null);
        }
        return $this->_cases;
    }
}