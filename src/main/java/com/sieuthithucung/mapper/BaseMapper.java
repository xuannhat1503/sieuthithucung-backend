package com.sieuthithucung.mapper;

import tools.jackson.databind.ObjectMapper;
import com.sieuthithucung.common.GenericEntityDtoMapper;

public class BaseMapper<E, D> extends GenericEntityDtoMapper<E, D> {

    public BaseMapper(ObjectMapper objectMapper, Class<E> entityClass, Class<D> dtoClass) {
        super(objectMapper, entityClass, dtoClass);
    }
}

