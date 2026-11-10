<?php
namespace touiteur\actions;

class DetailAction extends Action
{
    public function execute(): string
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $touite = $id ? (new \touiteur\GestionTouite($this->pdo))->getTouiteParId($id) : null;
        if ($touite === null) {
            http_response_code(404);
        }
        return $this->renderView('detail', ['touite' => $touite]);
    }
}
