package com.sieuthithucung.mapper;

import tools.jackson.databind.ObjectMapper;
import com.sieuthithucung.dto.ProductDto;
import com.sieuthithucung.entity.ProductEntity;
import org.springframework.stereotype.Component;

@Component
public class ProductMapper extends BaseMapper<ProductEntity, ProductDto> {

    public ProductMapper(ObjectMapper objectMapper) {
        super(objectMapper, ProductEntity.class, ProductDto.class);
    }
}

