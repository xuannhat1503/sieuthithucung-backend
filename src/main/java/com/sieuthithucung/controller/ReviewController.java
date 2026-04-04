package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.ReviewDto;
import com.sieuthithucung.service.ReviewService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/reviews")
public class ReviewController extends AbstractCrudController<Long, ReviewDto> {

    public ReviewController(ReviewService service) {
        super(service);
    }
}

