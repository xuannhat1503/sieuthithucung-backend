package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.ReviewDto;
import com.sieuthithucung.entity.ReviewEntity;

public class ReviewMapper {
    public static ReviewDto mapToReviewDto(ReviewEntity entity) {
        return new ReviewDto(
                entity.getId(),
                entity.getUserId(),
                entity.getProductId(),
                entity.getRating(),
                entity.getComment(),
                entity.getCreatedAt(),
                entity.getUpdatedAt()
        );
    }

    public static ReviewEntity mapToReviewEntity(ReviewDto dto) {
        return new ReviewEntity(
                dto.getId(),
                dto.getUserId(),
                dto.getProductId(),
                dto.getRating(),
                dto.getComment(),
                dto.getCreatedAt(),
                dto.getUpdatedAt()
        );
    }
}