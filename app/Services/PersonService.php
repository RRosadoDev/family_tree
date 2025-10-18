<?php

namespace App\Services;

use App\Repositories\PersonRepository;
use Exception;

class PersonService {
    public function __construct(protected PersonRepository $personRepository) {}

    public function getAll() {
        try {
            return $this->personRepository->getAll();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function store(array $data) { 
        try {
            return $this->personRepository->store($data);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function delete(int $idPerson) {
        try {
            return $this->personRepository->delete($idPerson);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function moveDescendants(int $childMoveId, ?int $newFatherId) {
        try {
            return $this->personRepository->moveDescendants($childMoveId, $newFatherId);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getLevelPerson(int $idPerson) {
        try {
            return $this->personRepository->calculatePersonLevel($idPerson);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getMaxDepth() {
        try {
            return $this->personRepository->calculateMaxDepth();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getAmountDescendants(int $idPerson) {
        try {
            return $this->personRepository->countDescendants($idPerson);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getDFS(int $idPerson) {
        try {
            return $this->personRepository->getDFS($idPerson);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getBFS(int $idPerson) {
        try {
            return $this->personRepository->getBFS($idPerson);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
