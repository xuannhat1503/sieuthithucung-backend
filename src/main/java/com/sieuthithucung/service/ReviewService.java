package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.ReviewDto;
import com.sieuthithucung.entity.ReviewEntity;
import com.sieuthithucung.mapper.ReviewMapper;
import com.sieuthithucung.repository.ReviewRepository;
import org.springframework.stereotype.Service;

@Service
public class ReviewService extends AbstractCrudService<ReviewEntity, Long, ReviewDto> {

    public ReviewService(ReviewRepository repository, ReviewMapper mapper) {
        super(repository, mapper, "Review");
    }
}

