package com.sieuthithucung.common;

import tools.jackson.databind.ObjectMapper;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;

import java.util.HashSet;
import java.util.Set;

public class GenericEntityDtoMapper<E, D> implements EntityDtoMapper<E, D> {

    private final ObjectMapper objectMapper;
    private final Class<E> entityClass;
    private final Class<D> dtoClass;

    public GenericEntityDtoMapper(ObjectMapper objectMapper, Class<E> entityClass, Class<D> dtoClass) {
        this.objectMapper = objectMapper;
        this.entityClass = entityClass;
        this.dtoClass = dtoClass;
    }

    @Override
    public E toEntity(D dto) {
        return objectMapper.convertValue(dto, entityClass);
    }

    @Override
    public D toDto(E entity) {
        return objectMapper.convertValue(entity, dtoClass);
    }

    @Override
    public void updateEntity(D dto, E entity) {
        E source = toEntity(dto);
        BeanUtils.copyProperties(source, entity, getNullPropertyNames(source));
    }

    private String[] getNullPropertyNames(Object source) {
        BeanWrapper src = new BeanWrapperImpl(source);
        java.beans.PropertyDescriptor[] pds = src.getPropertyDescriptors();

        Set<String> emptyNames = new HashSet<>();
        for (java.beans.PropertyDescriptor pd : pds) {
            Object srcValue = src.getPropertyValue(pd.getName());
            if (srcValue == null) {
                emptyNames.add(pd.getName());
            }
        }
        return emptyNames.toArray(new String[0]);
    }
}

