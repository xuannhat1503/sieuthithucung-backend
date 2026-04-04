package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.ReviewDto;
import com.sieuthithucung.entity.ReviewEntity;
import org.springframework.stereotype.Component;
import tools.jackson.databind.ObjectMapper;

@Component
public class ReviewMapper extends BaseMapper<ReviewEntity, ReviewDto> {

    public ReviewMapper(ObjectMapper objectMapper) {
        super(objectMapper, ReviewEntity.class, ReviewDto.class);
    }
}

