<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Church extends Model
{
    use HasFactory;
    protected $table = 'church'; 
    private int $id;
    private string $name;
    private string $cep;
    private string $street;
    private string $number;
    private ?string $complement;
    private string $quarter;
    private string $city;
    private string $state;

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getCep(): string { return $this->cep; }
    public function getStreet(): string { return $this->street; }
    public function getNumber(): string { return $this->number; }
    public function getComplement(): ?string { return $this->complement; }
    public function getQuarter(): string { return $this->quarter; }
    public function getCity(): string { return $this->city; }
    public function getState(): string { return $this->state; }

    public function setName(string $name): void { $this->name = $name; }
    public function setCep(string $cep): void { $this->cep = $cep; }
    public function setStreet(string $street): void { $this->street = $street; }
    public function setNumber(string $number): void { $this->number = $number; }
    public function setComplement(?string $complement): void { $this->complement = $complement; }
    public function setQuarter(string $quarter): void { $this->quarter = $quarter; }
    public function setCity(string $city): void { $this->city = $city; }
    public function setState(string $state): void { $this->state = $state; }
}
