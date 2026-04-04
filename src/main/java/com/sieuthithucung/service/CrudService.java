package com.sieuthithucung.service;

import java.util.List;

public interface CrudService<ID, D> {

    D create(D dto);

    D update(ID id, D dto);

    void delete(ID id);

    D findById(ID id);

    List<D> getAll();
}
