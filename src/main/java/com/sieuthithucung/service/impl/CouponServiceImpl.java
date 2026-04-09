package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.CouponDto;
import com.sieuthithucung.entity.CouponEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.CouponMapper;
import com.sieuthithucung.repository.CouponRepository;
import com.sieuthithucung.service.CouponService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class CouponServiceImpl implements CouponService {

    private final CouponRepository repository;

    public CouponServiceImpl(CouponRepository repository) {
        this.repository = repository;
    }

    @Override
    public CouponDto create(CouponDto dto) {
        return createInternal(dto);
    }

    @Override
    public CouponDto update(Long id, CouponDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Coupon not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public CouponDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<CouponDto> getAll() {
        return getAllInternal();
    }

    private CouponDto createInternal(CouponDto dto) {
        CouponEntity entity = CouponMapper.mapToCouponEntity(dto);
        CouponEntity saved = repository.save(entity);
        return CouponMapper.mapToCouponDto(saved);
    }

    private CouponDto updateInternal(Long id, CouponDto dto) {
        CouponEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Coupon not found with id: " + id));

        CouponEntity source = CouponMapper.mapToCouponEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        CouponEntity saved = repository.save(existing);
        return CouponMapper.mapToCouponDto(saved);
    }

    private CouponDto findByIdInternal(Long id) {
        CouponEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Coupon not found with id: " + id));
        return CouponMapper.mapToCouponDto(entity);
    }

    private List<CouponDto> getAllInternal() {
        return repository.findAll().stream().map(CouponMapper::mapToCouponDto).toList();
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
