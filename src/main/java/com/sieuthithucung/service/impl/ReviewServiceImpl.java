package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.ReviewDto;
import com.sieuthithucung.entity.ReviewEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.ReviewMapper;
import com.sieuthithucung.repository.ReviewRepository;
import com.sieuthithucung.service.ReviewService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class ReviewServiceImpl implements ReviewService {

    private final ReviewRepository repository;

    public ReviewServiceImpl(ReviewRepository repository) {
        this.repository = repository;
    }
    @Override
    public ReviewDto create(ReviewDto dto) {
        return createInternal(dto);
    }

    @Override
    public ReviewDto update(Long id, ReviewDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Review not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public ReviewDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<ReviewDto> getAll() {
        return getAllInternal();
    }

    private ReviewDto createInternal(ReviewDto dto) {
        ReviewEntity entity = ReviewMapper.mapToReviewEntity(dto);
        ReviewEntity saved = repository.save(entity);
        return ReviewMapper.mapToReviewDto(saved);
    }

    private ReviewDto updateInternal(Long id, ReviewDto dto) {
        ReviewEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Review not found with id: " + id));

        ReviewEntity source = ReviewMapper.mapToReviewEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        ReviewEntity saved = repository.save(existing);
        return ReviewMapper.mapToReviewDto(saved);
    }

    private ReviewDto findByIdInternal(Long id) {
        ReviewEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Review not found with id: " + id));
        return ReviewMapper.mapToReviewDto(entity);
    }

    private List<ReviewDto> getAllInternal() {
        return repository.findAll().stream().map(ReviewMapper::mapToReviewDto).toList();
    }

    private String[] getNullPropertyNames(Object source) {
        BeanWrapper src = new BeanWrapperImpl(source);
        PropertyDescriptor[] pds = src.getPropertyDescriptors();

        Set<String> emptyNames = new HashSet<>();
        for (PropertyDescriptor pd : pds) {
            Object srcValue = src.getPropertyValue(pd.getName());
            if (srcValue == null) {
                emptyNames.add(pd.getName());
            }
        }
        return emptyNames.toArray(new String[0]);
    }
}