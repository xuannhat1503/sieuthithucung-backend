package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.CategoryDto;
import com.sieuthithucung.service.CategoryService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/categories")
public class CategoryController extends AbstractCrudController<Long, CategoryDto> {

    public CategoryController(CategoryService service) {
        super(service);
    }
}

