package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.ProductImageDto;
import com.sieuthithucung.entity.ProductImageEntity;
import org.springframework.stereotype.Component;
import tools.jackson.databind.ObjectMapper;

@Component
public class ProductImageMapper extends BaseMapper<ProductImageEntity, ProductImageDto> {

    public ProductImageMapper(ObjectMapper objectMapper) {
        super(objectMapper, ProductImageEntity.class, ProductImageDto.class);
    }
}

