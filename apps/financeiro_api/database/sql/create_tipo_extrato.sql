
-- Tabela TipoExtrato (Id, Nome)
CREATE TABLE TipoExtrato (
  Id TINYINT UNSIGNED NOT NULL,
  Nome VARCHAR(50) NOT NULL,
  PRIMARY KEY (Id)
);

-- Dados iniciais
INSERT INTO TipoExtrato (Id, Nome) VALUES
  (1, 'Débito'),
  (2, 'Crédito');


alter table Extrato
    add constraint Extrato_TipoExtrato_Id_fk
        foreign key (IdTipoExtrato) references TipoExtrato (Id);
