package com.sieuthithucung.common;

public interface EntityDtoMapper<E, D> {

    E toEntity(D dto);

    D toDto(E entity);

    void updateEntity(D dto, E entity);
}

